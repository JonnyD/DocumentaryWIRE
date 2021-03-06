<?php

namespace App\Service;

use App\Criteria\VideoSourceCriteria;
use App\Entity\VideoSource;
use App\Enum\Sync;
use App\Enum\UpdateTimestamps;
use App\Repository\VideoSourceRepository;
use Doctrine\Common\Collections\ArrayCollection;

class VideoSourceService
{
    /**
     * @var VideoSourceRepository
     */
    private $videoSourceRepository;

    /**
     * @param VideoSourceRepository $videoSourceRepository
     */
    public function __construct(VideoSourceRepository $videoSourceRepository)
    {
        $this->videoSourceRepository = $videoSourceRepository;
    }

    /**
     * @param int $id
     * @return VideoSource|null
     */
    public function getVideoSourceById(int $id)
    {
        return $this->videoSourceRepository->find($id);
    }

    /**
     * @return VideoSource[]
     */
    public function getAllVideoSources()
    {
        return $this->videoSourceRepository->findAll();
    }

    /**
     * @param VideoSourceCriteria $criteria
     * @return VideoSource[]|ArrayCollection
     */
    public function getAllVideoSourcesByCriteria(VideoSourceCriteria $criteria)
    {
        return $this->videoSourceRepository->findVideoSourcesByCriteria($criteria);
    }

    /**
     * @param VideoSource $videoSource
     * @param string $updateTimestamps
     * @param string $sync
     * @return VideoSource|null
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(VideoSource $videoSource, string $updateTimestamps = UpdateTimestamps::YES, string $sync = Sync::YES)
    {
        if ($updateTimestamps === UpdateTimestamps::YES) {
            $currentDateTime = new \DateTime();

            if ($videoSource->getCreatedAt() == null) {
                $videoSource->setCreatedAt($currentDateTime);
            } else {
                $videoSource->setUpdatedAt($currentDateTime);
            }
        }

        $this->videoSourceRepository->save($videoSource, $sync);

        $videoSourceFromDatabase = $this->videoSourceRepository->find($videoSource->getId());
        return $videoSourceFromDatabase;
    }
}