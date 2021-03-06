<?php 

class CommentCest
{
    public function _before(ApiTester $I)
    {
    }

    public function listWithoutDocumentaryIdAsGuest(ApiTester $I)
    {
        $I->sendGET('api/v1/comment');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson();
        $I->seeResponseContains('Documentary ID is required');
    }

    public function listWithDocumentaryIdAsGuest(ApiTester $I)
    {
        $documentaryClass = \App\Entity\Documentary::class;
        /** @var \App\Entity\Documentary $documentary */
        $documentary = $I->grabEntityFromRepository($documentaryClass, [
            'slug' => 'documentary-2'
        ]);

        $I->sendGET('api/v1/comment?documentary='.$documentary->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContainsJson();
        $response = json_decode($I->grabResponse(), true)['items'];
        $I->assertEquals(1, count($response));

        $expectedResponse = [
            'items' => [
                'commentText' => 'This is a comment 4',
                'status' => 'published'
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listWithDocumentaryIdAndStatusAsGuest(ApiTester $I)
    {
        $documentaryClass = \App\Entity\Documentary::class;
        /** @var \App\Entity\Documentary $documentary */
        $documentary = $I->grabEntityFromRepository($documentaryClass, [
            'slug' => 'documentary-2'
        ]);

        $I->sendGET('api/v1/comment?documentary='.$documentary->getId().'&status='.\App\Enum\CommentStatus::PUBLISHED);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson();
        $I->seeResponseContains('Only admins can change status');
    }

    public function listWithDocumentaryIdAndIncorrectStatusAsGuest(ApiTester $I)
    {
        $documentaryClass = \App\Entity\Documentary::class;
        /** @var \App\Entity\Documentary $documentary */
        $documentary = $I->grabEntityFromRepository($documentaryClass, [
            'slug' => 'documentary-2'
        ]);

        $I->sendGET('api/v1/comment?documentary='.$documentary->getId().'&status=xxxxxxxxx');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson();
        $I->seeResponseContains('Status does not exist');
    }

    public function listWithIncorrectDocumentaryIdAsGuest(ApiTester $I)
    {
        $I->sendGET('api/v1/comment?documentary=999999999');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson();
        $I->seeResponseContains('Documentary cannot be found');
    }

    public function listWithDocumentaryIdAndUserAsGuest(ApiTester $I)
    {
        $userClass = \App\Entity\User::class;
        /** @var \App\Entity\User $user */
        $user = $I->grabEntityFromRepository($userClass, [
            'username' => 'user1'
        ]);

        $documentaryClass = \App\Entity\Documentary::class;
        /** @var \App\Entity\Documentary $documentary */
        $documentary = $I->grabEntityFromRepository($documentaryClass, [
            'slug' => 'documentary-2'
        ]);

        $I->sendGET('api/v1/comment?documentary='.$documentary->getId().'&user='.$user->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContainsJson();
        $response = json_decode($I->grabResponse(), true)['items'];
        $I->assertEquals(1, count($response));

        $expectedResponse = [
            'items' => [
                'commentText' => 'This is a comment 4',
                'status' => 'published'
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listWithDocumentaryIdAndIncorrectUserAsGuest(ApiTester $I)
    {
        $documentaryClass = \App\Entity\Documentary::class;
        /** @var \App\Entity\Documentary $documentary */
        $documentary = $I->grabEntityFromRepository($documentaryClass, [
            'slug' => 'documentary-2'
        ]);

        $I->sendGET('api/v1/comment?documentary='.$documentary->getId().'&user=99999999');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson();
        $I->seeResponseContains('User cannot be found');
    }

    public function listWithDocumentaryIdAsAdmin(ApiTester $I)
    {
        $username = 'user1';

        $logInDetails = [
            'grant_type' => 'password',
            'client_id' => '1_5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s',
            'client_secret' => 'sdgggskokererg4232404gc4csdgfdsgf8s8ck5s',
            'username' => $username,
            'password' => 'password'
        ];
        $I->sendPOST('oauth/v2/token', $logInDetails);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContains('access_token');
        $I->seeResponseContains('expires_in');
        $I->seeResponseContains('token_type');
        $I->seeResponseContains('scope');
        $I->seeResponseContains('refresh_token');

        $response = json_decode($I->grabResponse(), true);
        $accessToken = $response['access_token'];
        $I->amBearerAuthenticated($accessToken);

        $documentaryClass = \App\Entity\Documentary::class;
        /** @var \App\Entity\Documentary $documentary */
        $documentary = $I->grabEntityFromRepository($documentaryClass, [
            'slug' => 'documentary-2'
        ]);

        $I->sendGet('api/v1/comment?documentary='.$documentary->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContainsJson();
        $response = json_decode($I->grabResponse(), true)['items'];
        $I->assertEquals(2, count($response));

        $expectedResponse = [
            'items' => [
                [
                    'commentText' => 'This is a comment 3',
                    'status' => 'pending'
                ],
                [
                    'commentText' => 'This is a comment 4',
                    'status' => 'published'
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listWithoutDocumentaryIdAsAdmin(ApiTester $I)
    {
        $username = 'user1';

        $logInDetails = [
            'grant_type' => 'password',
            'client_id' => '1_5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s',
            'client_secret' => 'sdgggskokererg4232404gc4csdgfdsgf8s8ck5s',
            'username' => $username,
            'password' => 'password'
        ];
        $I->sendPOST('oauth/v2/token', $logInDetails);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContains('access_token');
        $I->seeResponseContains('expires_in');
        $I->seeResponseContains('token_type');
        $I->seeResponseContains('scope');
        $I->seeResponseContains('refresh_token');

        $response = json_decode($I->grabResponse(), true);
        $accessToken = $response['access_token'];
        $I->amBearerAuthenticated($accessToken);

        $I->sendGet('api/v1/comment');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContainsJson();
        $response = json_decode($I->grabResponse(), true)['items'];
        $I->assertEquals(6, count($response));

        $expectedResponse = [
            'items' => [
                [
                    'commentText' => 'This is a comment 1',
                    'status' => 'pending'
                ],
                [
                    'commentText' => 'This is a comment 2',
                    'status' => 'published'
                ],
                [
                    'commentText' => 'This is a comment 3',
                    'status' => 'pending'
                ],
                [
                    'commentText' => 'This is a comment 4',
                    'status' => 'published'
                ],
                [
                    'commentText' => 'This is a comment 5',
                    'status' => 'pending'
                ],
                [
                    'commentText' => 'This is a comment 6',
                    'status' => 'published'
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listWithoutDocumentaryIdStatusPublishedAsAdmin(ApiTester $I)
    {
        $username = 'user1';

        $logInDetails = [
            'grant_type' => 'password',
            'client_id' => '1_5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s',
            'client_secret' => 'sdgggskokererg4232404gc4csdgfdsgf8s8ck5s',
            'username' => $username,
            'password' => 'password'
        ];
        $I->sendPOST('oauth/v2/token', $logInDetails);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContains('access_token');
        $I->seeResponseContains('expires_in');
        $I->seeResponseContains('token_type');
        $I->seeResponseContains('scope');
        $I->seeResponseContains('refresh_token');

        $response = json_decode($I->grabResponse(), true);
        $accessToken = $response['access_token'];
        $I->amBearerAuthenticated($accessToken);

        $I->sendGet('api/v1/comment?status='.\App\Enum\CommentStatus::PUBLISHED);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContainsJson();
        $response = json_decode($I->grabResponse(), true)['items'];
        $I->assertEquals(3, count($response));

        $expectedResponse = [
            'items' => [
                [
                    'commentText' => 'This is a comment 2',
                    'status' => 'published'
                ],
                [
                    'commentText' => 'This is a comment 4',
                    'status' => 'published'
                ],
                [
                    'commentText' => 'This is a comment 6',
                    'status' => 'published'
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listWithoutDocumentaryIdStatusPendingAsAdmin(ApiTester $I)
    {
        $username = 'user1';

        $logInDetails = [
            'grant_type' => 'password',
            'client_id' => '1_5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s',
            'client_secret' => 'sdgggskokererg4232404gc4csdgfdsgf8s8ck5s',
            'username' => $username,
            'password' => 'password'
        ];
        $I->sendPOST('oauth/v2/token', $logInDetails);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContains('access_token');
        $I->seeResponseContains('expires_in');
        $I->seeResponseContains('token_type');
        $I->seeResponseContains('scope');
        $I->seeResponseContains('refresh_token');

        $response = json_decode($I->grabResponse(), true);
        $accessToken = $response['access_token'];
        $I->amBearerAuthenticated($accessToken);

        $I->sendGet('api/v1/comment?status='.\App\Enum\CommentStatus::PENDING);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContainsJson();
        $response = json_decode($I->grabResponse(), true)['items'];
        $I->assertEquals(3, count($response));

        $expectedResponse = [
            'items' => [
                [
                    'commentText' => 'This is a comment 1',
                    'status' => 'pending'
                ],
                [
                    'commentText' => 'This is a comment 3',
                    'status' => 'pending'
                ],
                [
                    'commentText' => 'This is a comment 5',
                    'status' => 'pending'
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function getCommentNotLoggedIn(ApiTester $I)
    {
        $commentClass = \App\Entity\Comment::class;
        /** @var \App\Entity\Comment $comment */
        $comment = $I->grabEntityFromRepository($commentClass, [
            'commentText' => 'This is a comment 2',
            'status' => 'published'
        ]);

        $I->sendGET('api/v1/comment/'.$comment->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContainsJson();

        $expectedResponse = [
            'commentText' => 'This is a comment 2',
            'status' => 'published',
            'user' => [
                'username' => 'user2'
            ],
            'documentary' => [
                'title' => 'Documentary 1'
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function getCommentIsPendingNotLoggedIn(ApiTester $I)
    {
        $commentClass = \App\Entity\Comment::class;
        /** @var \App\Entity\Comment $comment */
        $comment = $I->grabEntityFromRepository($commentClass, [
            'commentText' => 'This is a comment 1',
            'status' => 'pending'
        ]);

        $I->sendGET('api/v1/comment/'.$comment->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Unauthorized to view this comment');
    }

    public function getCommentIsPendingAsAdmin(ApiTester $I)
    {
        $username = 'user1';

        $logInDetails = [
            'grant_type' => 'password',
            'client_id' => '1_5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s',
            'client_secret' => 'sdgggskokererg4232404gc4csdgfdsgf8s8ck5s',
            'username' => $username,
            'password' => 'password'
        ];
        $I->sendPOST('oauth/v2/token', $logInDetails);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContains('access_token');
        $I->seeResponseContains('expires_in');
        $I->seeResponseContains('token_type');
        $I->seeResponseContains('scope');
        $I->seeResponseContains('refresh_token');

        $response = json_decode($I->grabResponse(), true);
        $accessToken = $response['access_token'];
        $I->amBearerAuthenticated($accessToken);

        $commentClass = \App\Entity\Comment::class;
        /** @var \App\Entity\Comment $comment */
        $comment = $I->grabEntityFromRepository($commentClass, [
            'commentText' => 'This is a comment 1',
            'status' => 'pending'
        ]);

        $I->sendGET('api/v1/comment/'.$comment->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'commentText' => 'This is a comment 1',
            'status' => 'pending',
            'user' => [
                'username' => 'user1'
            ],
            'documentary' => [
                'title' => 'Documentary 1'
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function editCommentAsGuest(ApiTester $I)
    {
        $I->sendPATCH('api/v1/comment/1');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson();
        $I->seeResponseContains('Comment not found');
    }

    public function editCommentNotFoundAsAdmin(ApiTester $I)
    {
        $username = 'user1';

        $logInDetails = [
            'grant_type' => 'password',
            'client_id' => '1_5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s',
            'client_secret' => 'sdgggskokererg4232404gc4csdgfdsgf8s8ck5s',
            'username' => $username,
            'password' => 'password'
        ];
        $I->sendPOST('oauth/v2/token', $logInDetails);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContains('access_token');
        $I->seeResponseContains('expires_in');
        $I->seeResponseContains('token_type');
        $I->seeResponseContains('scope');
        $I->seeResponseContains('refresh_token');

        $response = json_decode($I->grabResponse(), true);
        $accessToken = $response['access_token'];
        $I->amBearerAuthenticated($accessToken);

        $I->sendPATCH('api/v1/comment/99999999');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson();
        $I->seeResponseContains('Comment not found');
    }

    public function editCommentFoundAsAdmin(ApiTester $I)
    {
        $username = 'user1';

        $logInDetails = [
            'grant_type' => 'password',
            'client_id' => '1_5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s',
            'client_secret' => 'sdgggskokererg4232404gc4csdgfdsgf8s8ck5s',
            'username' => $username,
            'password' => 'password'
        ];
        $I->sendPOST('oauth/v2/token', $logInDetails);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContains('access_token');
        $I->seeResponseContains('expires_in');
        $I->seeResponseContains('token_type');
        $I->seeResponseContains('scope');
        $I->seeResponseContains('refresh_token');

        $response = json_decode($I->grabResponse(), true);
        $accessToken = $response['access_token'];
        $I->amBearerAuthenticated($accessToken);

        /** @var \App\Entity\Comment $comment */
        $comment = $I->grabEntityFromRepository(\App\Entity\Comment::class, [
            'commentText' => 'This is a comment 1',
            'status' => 'pending'
        ]);
        $data = [
            'commentText' => 'This is a comment xxxxx',
            'status' => 'published'
        ];
        $I->sendPATCH('api/v1/comment/' . $comment->getId(), json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContainsJson();

        $expectedResponse = [
            'commentText' => 'This is a comment xxxxx',
            'status' => 'published',
            'user' => [
                'username' => 'user1'
            ],
            'documentary' => [
                'title' => 'Documentary 1'
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);

        $data = [
            'commentText' => 'This is a comment 1',
            'status' => 'pending'
        ];
        $I->sendPATCH('api/v1/comment/' . $comment->getId(), json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
    }

    public function editCommentAsOwner(ApiTester $I)
    {
        $username = 'user3';

        $logInDetails = [
            'grant_type' => 'password',
            'client_id' => '1_5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s',
            'client_secret' => 'sdgggskokererg4232404gc4csdgfdsgf8s8ck5s',
            'username' => $username,
            'password' => 'password'
        ];
        $I->sendPOST('oauth/v2/token', $logInDetails);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContains('access_token');
        $I->seeResponseContains('expires_in');
        $I->seeResponseContains('token_type');
        $I->seeResponseContains('scope');
        $I->seeResponseContains('refresh_token');

        $response = json_decode($I->grabResponse(), true);
        $accessToken = $response['access_token'];
        $I->amBearerAuthenticated($accessToken);

        /** @var \App\Entity\Comment $comment */
        $comment = $I->grabEntityFromRepository(\App\Entity\Comment::class, [
            'commentText' => 'This is a comment 3',
            'status' => 'pending'
        ]);
        $data = [
            'commentText' => 'This is a comment xxxxx',
            'status' => 'pending'
        ];
        $I->sendPATCH('api/v1/comment/' . $comment->getId(), json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContainsJson();

        $expectedResponse = [
            'commentText' => 'This is a comment xxxxx',
            'status' => 'pending',
            'user' => [
                'username' => 'user3'
            ],
            'documentary' => [
                'title' => 'Documentary 2'
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);

        $data = [
            'commentText' => 'This is a comment 3',
            'status' => 'pending'
        ];
        $I->sendPATCH('api/v1/comment/' . $comment->getId(), json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
    }

    public function editCommentAsOwnerDifferentStatus(ApiTester $I)
    {
        $username = 'user3';

        $logInDetails = [
            'grant_type' => 'password',
            'client_id' => '1_5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s',
            'client_secret' => 'sdgggskokererg4232404gc4csdgfdsgf8s8ck5s',
            'username' => $username,
            'password' => 'password'
        ];
        $I->sendPOST('oauth/v2/token', $logInDetails);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContains('access_token');
        $I->seeResponseContains('expires_in');
        $I->seeResponseContains('token_type');
        $I->seeResponseContains('scope');
        $I->seeResponseContains('refresh_token');

        $response = json_decode($I->grabResponse(), true);
        $accessToken = $response['access_token'];
        $I->amBearerAuthenticated($accessToken);

        /** @var \App\Entity\Comment $comment */
        $comment = $I->grabEntityFromRepository(\App\Entity\Comment::class, [
            'commentText' => 'This is a comment 3',
            'status' => 'pending'
        ]);
        $data = [
            'commentText' => 'This is a comment xxxxx',
            'status' => 'published'
        ];
        $I->sendPATCH('api/v1/comment/' . $comment->getId(), json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
        $I->seeResponseContains('Only admins can edit comment status');

        $data = [
            'commentText' => 'This is a comment 3',
            'status' => 'pending'
        ];
        $I->sendPATCH('api/v1/comment/' . $comment->getId(), json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
    }

    public function createComment(ApiTester $I)
    {
        //@TODO
    }
}
