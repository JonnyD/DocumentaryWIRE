<?php

namespace App\Controller;

use App\Criteria\EmailCriteria;
use App\Entity\Category;
use App\Entity\Email;
use App\Enum\EmailOrderBy;
use App\Enum\EmailSource;
use App\Enum\Subscribed;
use App\Form\CategoryForm;
use App\Form\EmailForm;
use App\Form\UnsubscribeEmailSubscriptionForm;
use App\Hydrator\EmailHydrator;
use App\Service\CategoryService;
use App\Service\EmailService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Request;

class EmailSubscriptionController extends BaseController implements ClassResourceInterface
{
    /**
     * @var EmailService
     */
    private $emailService;

    /**
     * @param EmailService $emailService
     */
    public function __construct(
        EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * @FOSRest\Get("/email", name="get_emails", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $page = $request->query->get('page', 1);

        $criteria = new EmailCriteria();

        $sort = $request->query->get('sort');
        if (isset($sort)) {
            $exploded = explode("-", $sort);
            $orderBy = $exploded[0];
            $direction = $exploded[1];

            $hasOrderBy = EmailOrderBy::hasOrderBy($orderBy);
            if (!$hasOrderBy) {
                return $this->createApiResponse('Order by ' . $orderBy . ' does not exist', 404);
            }

            $sort = [$orderBy => $direction];
            $criteria->setSort($sort);
        }

        $subscribed = $request->query->get('subscribed');
        if (isset($subscribed)) {
            $hasSubscribed = Subscribed::hasStatus($subscribed);
            if (!$hasSubscribed) {
                return $this->createApiResponse('Subscribed status ' . $subscribed . ' does not exist', 404);
            }
            $criteria->setSubscribed($subscribed);
        }

        $source = $request->query->get('source');
        if (isset($source)) {
            $hasEmailSource = EmailSource::hasEmailSource($source);
            if (!$hasEmailSource) {
                return $this->createApiResponse('Email source ' . $source . ' does not exist', 404);
            }
            $criteria->setSource($source);
        }

        $email = $request->query->get('email');
        if (isset($email)) {
            $criteria->setEmail($email);
        }

        $qb = $this->emailService->getEmailsByCriteriaQueryBuilder($criteria);

        $adapter = new DoctrineORMAdapter($qb, false);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(16);
        $pagerfanta->setCurrentPage($page);

        $items = (array) $pagerfanta->getCurrentPageResults();

        $serialized = $this->serializeEmails($items);

        $data = [
            'items'             => $serialized,
            'count_results'     => $pagerfanta->getNbResults(),
            'current_page'      => $pagerfanta->getCurrentPage(),
            'number_of_pages'   => $pagerfanta->getNbPages(),
            'next'              => ($pagerfanta->hasNextPage()) ? $pagerfanta->getNextPage() : null,
            'prev'              => ($pagerfanta->hasPreviousPage()) ? $pagerfanta->getPreviousPage() : null,
            'paginate'          => $pagerfanta->haveToPaginate(),
        ];

        return $this->createApiResponse($data, 200);
    }

    /**
     * @FOSRest\Get("/email/{id}", name="get_email", options={ "method_prefix" = false })
     *
     * @param int $id
     * @return Email|null
     */
    public function getEmailAction(int $id)
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $email = $this->emailService->getEmailById($id);
        if ($email == null) {
            return $this->createApiResponse('Email not found', 404);
        }

        $emailHydrator = new EmailHydrator($email);
        $serialized = $emailHydrator->toArray();

        return $this->createApiResponse($serialized, 200);
    }

    /**
     * @FOSRest\Post("/email", name="create_email", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createEmailAction(Request $request)
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $email = new Email();

        $form = $this->createForm(EmailForm::class, $email);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $form->submit($data);

            if ($form->isSubmitted() && $form->isValid()) {
                $doesEmailAlreadyExist = $this->emailService->getEmailByEmailAddress($email->getEmail());
                if ($doesEmailAlreadyExist) {
                    return $this->createApiResponse('Email already exists', 400);
                }

                $subscriptionKey = sha1(mt_rand(10000,99999).time().$email->getEmail());
                $email->setSubscriptionKey($subscriptionKey);
                $email->setSubscribed(Subscribed::YES);
                $this->emailService->save($email);

                $emailHydrator = new EmailHydrator($email);
                $serialized = $emailHydrator->toArray();
                return $this->createApiResponse($serialized, 200);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return $this->createApiResponse($errors, 400);
            }
        }
    }

    /**
     * @FOSRest\Patch("/email/{id}", name="partial_update_email", options={ "method_prefix" = false })
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function editEmailAction(int $id, Request $request)
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $email = $this->emailService->getEmailById($id);
        if ($email === null) {
            return $this->createApiResponse('Email does not exist', 404);
        }

        $form = $this->createForm(EmailForm::class, $email);
        $form->handleRequest($request);

        if ($request->isMethod('PATCH')) {
            $data = json_decode($request->getContent(), true);
            $subscribed = $data['subscribed'];
            $form->submit($data);

            if ($form->isSubmitted() && $form->isValid()) {
                $email->setSubscribed($subscribed);
                $this->emailService->save($email);

                $emailHydrator = new EmailHydrator($email);
                $serialized = $emailHydrator->toArray();
                return $this->createApiResponse($serialized, 200);
            } else {
                $errors = (string)$form->getErrors(true, false);
                return $this->createApiResponse($errors, 400);
            }
        }

    }

    /**
     * @FOSRest\Get("/email/unsubscribe", name="unsubscribe", options={ "method_prefix" = false })
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unsubscribeAction(Request $request)
    {
        $emailAddress = $request->query->get('email');
        if (!$emailAddress) {
            return $this->createApiResponse("Email not found", 404);
        } else {
            $existingEmail = $this->emailService->getEmailByEmailAddress($emailAddress);
            if (!$existingEmail) {
                return $this->createApiResponse("Email not found", 404);
            }
        }

        $subscriptionKey = $request->query->get('subscription_key');
        if (!$subscriptionKey) {
            return $this->createApiResponse("Subscription key not found", 404);
        } else {
            $existingEmail = $this->emailService->getEmailByEmailAddressAndSubscriptionKey($emailAddress, $subscriptionKey);
            if (!$existingEmail) {
                return $this->createApiResponse("Subscription key not found", 404);
            }
        }

        $unsubscribeData = [
            'email' => $emailAddress,
            'subscriptionKey' => $subscriptionKey
        ];

        $form = $this->createForm(UnsubscribeEmailSubscriptionForm::class, $unsubscribeData);
        $form->handleRequest($request);

        $form->submit($unsubscribeData);

        if ($form->isSubmitted() && $form->isValid()) {
            $unsubscribed = $this->emailService->unsubscribe($emailAddress);
            if ($unsubscribed) {
                return $this->createApiResponse("Email Unsubscribed", 200);
            } else {
                return $this->createApiResponse("An error occurred", 400);
            }
        } else {
            $errors = (string)$form->getErrors(true, false);
            return new JsonResponse($errors, 400);
        }
    }

    /**
     * @param $emails
     * @return array
     */
    private function serializeEmails($emails)
    {
        $serialized = [];

        foreach ($emails as $email) {
            $emailHydrator = new EmailHydrator($email);
            $serialized[] = $emailHydrator->toArray();
        }

        return $serialized;
    }
}