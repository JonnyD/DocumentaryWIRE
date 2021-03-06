<?php

namespace App\Service;

use App\Criteria\DocumentaryCriteria;
use App\Entity\Category;
use App\Entity\Documentary;
use App\Entity\DocumentaryVideoSource;
use App\Entity\Movie;
use App\Enum\DocumentaryOrderBy;
use App\Enum\DocumentaryStatus;
use App\Enum\Featured;
use App\Enum\IsParent;
use App\Enum\Order;
use App\Enum\Sync;
use App\Enum\UpdateTimestamps;
use App\Event\DocumentaryEvent;
use App\Event\DocumentaryEvents;
use App\Repository\DocumentaryRepository;
use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use PhpParser\Comment\Doc;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DocumentaryService
{
    /**
     * @var DocumentaryRepository
     */
    private $documentaryRepository;

    /**
     * @var MovieRepository
     */
    private $movieRepository;

    /**
     * @var VideoSourceService
     */
    private $videoSourceService;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param DocumentaryRepository $documentaryRepository
     * @param MovieRepository $movieRepository
     * @param VideoSourceService $videoSourceService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        DocumentaryRepository $documentaryRepository,
        MovieRepository $movieRepository,
        VideoSourceService $videoSourceService,
        EventDispatcherInterface $eventDispatcher)
    {
        $this->documentaryRepository = $documentaryRepository;
        $this->movieRepository = $movieRepository;
        $this->videoSourceService = $videoSourceService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return Documentary[]
     */
    public function getAllDocumentaries()
    {
        return $this->documentaryRepository->findAll();
    }

    /**
     * @param string $slug
     * @return Documentary|null
     */
    public function getDocumentaryBySlug(string $slug)
    {
        return $this->documentaryRepository->findOneBy([
            "slug" => $slug
        ]);
    }

    /**
     * @param DocumentaryCriteria $criteria
     * @return Documentary[]|ArrayCollection
     */
    public function getDocumentariesByCriteria(DocumentaryCriteria $criteria)
    {
        return $this->documentaryRepository->findDocumentariesByCriteria($criteria);
    }

    /**
     * @param DocumentaryCriteria $criteria
     * @return QueryBuilder
     */
    public function getDocumentariesByCriteriaQueryBuilder(DocumentaryCriteria $criteria)
    {
        return $this->documentaryRepository->findDocumentariesByCriteriaQueryBuilder($criteria);
    }

    /**
     * @return ArrayCollection|Documentary[]
     */
    public function getFeaturedDocumentaries()
    {
        $criteria = new DocumentaryCriteria();
        $criteria->setFeatured(Featured::YES);
        $criteria->setStatus(DocumentaryStatus::PUBLISHED);

        $documentaries = $this->documentaryRepository->findDocumentariesByCriteria($criteria);
        shuffle($documentaries);
        return $documentaries;
    }

    /**
     * @return Documentary
     */
    public function getLatestDocumentary()
    {
        $criteria = new DocumentaryCriteria();
        $criteria->setLimit(1);
        $criteria->setSort([
            DocumentaryOrderBy::UPDATED_AT => Order::DESC
        ]);
        $criteria->setStatus(DocumentaryStatus::PUBLISHED);

        return $this->documentaryRepository->findDocumentaryByCriteria($criteria);
    }

    /**
     * @param Category $category
     * @return ArrayCollection|Documentary[]
     */
    public function getPublishedDocumentariesInCategory(Category $category)
    {
        $criteria = new DocumentaryCriteria();
        $criteria->setStatus(DocumentaryStatus::PUBLISHED);
        $criteria->setCategory($category);
        $criteria->setSort([
            DocumentaryOrderBy::CREATED_AT => Order::DESC
        ]);

        return $this->documentaryRepository->findDocumentariesByCriteria($criteria);
    }

    /**
     * @param Category $category
     * @return Documentary
     */
    public function getLatestDocumentaryInCategory(Category $category)
    {
        $criteria = new DocumentaryCriteria();
        $criteria->setStatus(DocumentaryStatus::PUBLISHED);
        $criteria->setCategory($category);
        $criteria->setLimit(1);
        $criteria->setSort([
            DocumentaryOrderBy::UPDATED_AT => Order::DESC
        ]);

        return $this->documentaryRepository->findDocumentaryByCriteria($criteria);
    }

    /**
     * @param int $limit
     * @return ArrayCollection|Documentary[]
     */
    public function getMostPopularDocumentaries(int $limit)
    {
        $criteria = new DocumentaryCriteria();
        $criteria->setLimit($limit);
        $criteria->setStatus(DocumentaryStatus::PUBLISHED);
        $criteria->setSort([
            DocumentaryOrderBy::VIEWS => Order::DESC
        ]);

        return $this->documentaryRepository->findDocumentariesByCriteria($criteria);
    }

    /**
     * @param int $limit
     * @return ArrayCollection|Documentary[]
     */
    public function getMostDiscussedDocumentaries(int $limit)
    {
        $criteria = new DocumentaryCriteria();
        $criteria->setLimit($limit);
        $criteria->setStatus(DocumentaryStatus::PUBLISHED);
        $criteria->setSort([
            DocumentaryOrderBy::COMMENT_COUNT => Order::DESC
        ]);

        return $this->documentaryRepository->findDocumentariesByCriteria($criteria);
    }

    /**
     * @param int $limit
     * @return ArrayCollection|Documentary[]
     */
    public function getMostWatchlistedDocumentaries(int $limit): array
    {
        $criteria = new DocumentaryCriteria();
        $criteria->setLimit($limit);
        $criteria->setStatus(DocumentaryStatus::PUBLISHED);
        $criteria->setSort([
            DocumentaryOrderBy::WATCHLIST_COUNT => Order::DESC
        ]);

        return $this->documentaryRepository->findDocumentariesByCriteria($criteria);
    }

    /**
     * @param Category $category
     * @param int $limit
     * @return ArrayCollection|Documentary[]
     */
    public function getRandomDocumentariesInCategoryByCriteria(Category $category, int $limit): array
    {
        $criteria = new DocumentaryCriteria();
        $criteria->setCategory($category);
        $criteria->setLimit($limit);
        $criteria->setStatus(DocumentaryStatus::PUBLISHED);
        $criteria->setSort([
            DocumentaryOrderBy::RANDOM => Order::ASC
        ]);

        return $this->documentaryRepository->findDocumentariesByCriteria($criteria);
    }

    /**
     * @return ArrayCollection|Documentary[]
     */
    public function getPublishedDocumentaries()
    {
        $criteria = new DocumentaryCriteria();
        $criteria->setStatus(DocumentaryStatus::PUBLISHED);
        $criteria->setSort([
            DocumentaryOrderBy::CREATED_AT => Order::DESC
        ]);

        return $this->documentaryRepository->findDocumentariesByCriteria($criteria);
    }

    /**
     * @param int $limit
     * @return ArrayCollection|Documentary[]
     */
    public function getLatestDocumentaries(int $limit)
    {
        $criteria = new DocumentaryCriteria();
        $criteria->setStatus(DocumentaryStatus::PUBLISHED);
        $criteria->setLimit($limit);
        $criteria->setIsParent(IsParent::YES);
        $criteria->setSort([
            DocumentaryOrderBy::CREATED_AT => Order::DESC
        ]);

        return $this->documentaryRepository->findDocumentariesByCriteria($criteria);
    }

    /**
     * @param int $limit
     * @return ArrayCollection|Documentary[]
     */
    public function getLatestDocumentariesInCategory(Category $category, int $limit)
    {
        $criteria = new DocumentaryCriteria();
        $criteria->setCategory($category);
        $criteria->setStatus(DocumentaryStatus::PUBLISHED);
        $criteria->setLimit($limit);
        $criteria->setIsParent(IsParent::YES);
        $criteria->setSort([
            DocumentaryOrderBy::CREATED_AT => Order::DESC
        ]);

        return $this->documentaryRepository->findDocumentariesByCriteria($criteria);
    }

    /**
     * @return Documentary|mixed
     */
    public function getLastUpdatedDocumentary()
    {
        $latestDocumentaries = $this->getLatestDocumentaries(1);
        $latestDocumentary = $latestDocumentaries[0];

        return $latestDocumentary;
    }

    /**
     * @param Category $category
     * @return Documentary|mixed
     */
    public function getLastUpdatedDocumentaryInCategory(Category $category)
    {
        $latestDocumentaries = $this->getLatestDocumentariesInCategory($category, 1);
        $latestDocumentary = $latestDocumentaries[0];

        return $latestDocumentary;
    }

    /**
     * @param string $video_src
     * @param string $video_url
     * @param int $video_width
     * @param int $video_height
     * @param $autoplay
     * @return mixed
     */
    public function getEmbedCode(string $video_src, string $video_url, int $video_width, int $video_height, $autoplay)
    {
        $embeds = array(
            "56" => '<embed src="http://player.56.com/' . $video_url . '.swf" type="application/x-shockwave-flash" width="' . $video_width . '" height="' . $video_height . '" allowNetworking="all" allowScriptAccess="always"></embed>',
            "blip" => '<embed src="http://blip.tv/play/' . $video_url . '" type="application/x-shockwave-flash" width="' . $video_width . '" height="' . $video_height . '" allowscriptaccess="always" allowfullscreen="true"></embed>',
            "dailymotion" => '<iframe frameborder="0" width="' . $video_width . '" height="' . $video_height . '" src="http://www.dailymotion.com/embed/video/' . $video_url . '?width=' . $video_width . '"></iframe>',
            "disclose" => '<object id="dtvplayer" width="' . $video_width . '" height="' . $video_height . '"> 	<param name="movie" value="http://www.disclose.tv/swf/player.swf" />  	<param name="wmode" value="transparent" /> 	<param name="allowFullScreen" value="true" />   <param name="allowscriptaccess" value="always" />    	<param name="flashvars"  		value="config=http://www.disclose.tv/videos/config/flv/' . $video_url . '.js" />  	<embed type="application/x-shockwave-flash" width="' . $video_width . '" height="' . $video_height . '" allowFullScreen="true"  	src="http://www.disclose.tv/swf/player.swf" 	flashvars="config=http://www.disclose.tv/videos/config/flv/' . $video_url . '.js"/></embed></object>',
            "embed" => $video_url,
            "forum-network" => '<object name="kaltura_player_1304432839" id="kaltura_player_1304432839" type="application/x-shockwave-flash" allowScriptAccess="always" allowNetworking="all" allowFullScreen="true" height="' . $video_height . '" width="' . $video_width . '" data="http://www.kaltura.com/index.php/kwidget/wid/1_rdxpkc3j/uiconf_id/' . $video_url . '"><param name="allowScriptAccess" value="always" /><param name="allowNetworking" value="all" /><param name="allowFullScreen" value="true" /><param name="bgcolor" value="#000000" /><param name="movie" value="http://www.kaltura.com/index.php/kwidget/wid/1_rdxpkc3j/uiconf_id/' . $video_url . '"/><param name="flashVars" value=""/></object>',
            "google" => '<embed style="width:' . $video_width . 'px; height:' . $video_height . 'px; id="VideoPlayback" type="application/x-shockwave-flash" src="http://video.google.com/googleplayer.swf?docId=' . $video_url . '" allowFullScreen="true" flashvars="fs=true"> </embed>',
            "krishnatube" => '<embed src="http://krishnatube.com/nvplayer.swf?config=http://krishnatube.com/nuevo/econfig.php?key=' . $video_url . '" width="' . $video_width . '" height="' . $video_height . '" wmode="transparent" allowscriptaccess="always" allowfullscreen="true" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" />',
            "megavideo" => '<object width="' . $video_width . '" height="' . $video_height . '"><param name="movie" value="' . $video_url . '"></param><param name="allowFullScreen" value="true"></param><embed src="' . $video_url . '" type="application/x-shockwave-flash" allowfullscreen="true" width="' . $video_width . '" height="' . $video_height . '"></embed></object>',
            "myspace" => '<object width="' . $video_width . 'px" height="' . $video_height . 'px" ><param name="allowScriptAccess" value="always"/><param name="allowFullScreen" value="true"/><param name="wmode" value="transparent"/><param name="movie" value="http://mediaservices.myspace.com/services/media/embed.aspx/m=' . $video_url . ',t=1,mt=video"/><embed src="http://mediaservices.myspace.com/services/media/embed.aspx/m=' . $video_url . ',t=1,mt=video" width="' . $video_width . '" height="' . $video_height . '" allowFullScreen="true" type="application/x-shockwave-flash" wmode="transparent" allowScriptAccess="always"></embed></object>',
            "novamov" => '<iframe style="overflow: hidden; border: 0; width: ' . $video_width . 'px; height: ' . $video_height . 'px" src="http://embed.novamov.com/embed.php?width=' . $video_width . '&height=' . $video_height . '&v=' . $video_url . '&px=1" scrolling="no"></iframe>',
            "pbs" => '<object width = "' . $video_width . '" height = "' . $video_height . '" > <param name = "movie" value = "http://www-tc.pbs.org/video/media/swf/PBSPlayer.swf" > </param><param name="flashvars" value="video=' . $video_url . '&player=viral" /> <param name="allowFullScreen" value="true"></param > <param name = "allowscriptaccess" value = "always" > </param><param name="wmode" value="transparent"></param ><embed src="http://www-tc.pbs.org/video/media/swf/PBSPlayer.swf" flashvars="video=' . $video_url . '&player=viral" type="application/x-shockwave-flash" allowscriptaccess="always" wmode="transparent" allowfullscreen="true" width="' . $video_width . '" height="' . $video_height . '" bgcolor="#000000"></embed></object>',
            "rutube" => '<OBJECT width="' . $video_width . '" height="' . $video_height . '"><PARAM name="movie" value="http://video.rutube.ru/' . $video_url . '"></PARAM><PARAM name="wmode" value="window"></PARAM><PARAM name="allowFullScreen" value="true"></PARAM><EMBED src="http://video.rutube.ru/' . $video_url . '" type="application/x-shockwave-flash" wmode="window" width="' . $video_width . '" height="' . $video_height . '" allowFullScreen="true" ></EMBED></OBJECT>',
            "sevenload" => '<script type="text/javascript" src="http://de.sevenload.com/pl/' . $video_url . '/' . $video_width . 'x' . $video_height . '"></script>',
            "snagfilms" => '<object width="' . $video_width . '" height="' . $video_height . '" data="http://o.snagfilms.com/film.swf" type="application/x-shockwave-flash" id="f-183"><param name="allowNetworking" value="all" /><param name="allowScriptAccess" value="always" /><param name="movie" value="http://o.snagfilms.com/film.swf" /><param name="wmode" value="transparent" /><param name="flashvars" value="id=183&cid=f-183-off_the_grid" /></object>',
            "stagevu" => '<iframe style="overflow: hidden; border: 0; width: ' . $video_width . 'px; height: ' . $video_height . 'px" src="http://stagevu.com/embed?width=' . $video_width . '&amp;height=' . $video_height . '&amp;background=000&amp;uid=' . $video_url . '" scrolling="yes"></iframe>',
            "tudou" => '<embed src="http://www.tudou.com/v/' . $video_url . '/v.swf" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" wmode="opaque" width="' . $video_width . '" height="' . $video_height . '"></embed>',
            "veoh" => '<object width="' . $video_width . '" height="' . $video_height . '" id="veohFlashPlayer" name="veohFlashPlayer"><param name="movie" value="http://www.veoh.com/static/swf/webplayer/WebPlayer.swf?version=AFrontend.5.5.4.1015&permalinkId=' . $video_url . '&player=videodetailsembedded&videoAutoPlay=0&id=anonymous"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.veoh.com/static/swf/webplayer/WebPlayer.swf?version=AFrontend.5.5.4.1015&permalinkId=' . $video_url . '&player=videodetailsembedded&videoAutoPlay=0&id=anonymous" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="' . $video_width . '" height="' . $video_height . '" id="veohFlashPlayerEmbed" name="veohFlashPlayerEmbed"></embed></object>',
            "viddler" => '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="' . $video_width . '" height="' . $video_height . '" id="viddler_7b5ea40d"><param name="movie" value="http://www.viddler.com/simple/' . $video_url . '/" /><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="true" /><embed src="http://www.viddler.com/simple/' . $video_url . '/" width="' . $video_width . '" height="' . $video_height . '" type="application/x-shockwave-flash" allowScriptAccess="always" allowFullScreen="true" name="viddler_7b5ea40d"></embed></object>',
            "vimeo" => '<iframe src="http://player.vimeo.com/video/' . $video_url . '" width="' . $video_width . '" height="' . $video_height . '" frameborder="0"></iframe>',
            "youtube playlist" => '<object width="' . $video_width . '" height="' . $video_height . '"><param name="movie" value="http://www.youtube.com/p/' . $video_url . '?hl=en_GB&fs=1&hd=1"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/p/' . $video_url . '?hl=en_GB&fs=1&hd=1" type="application/x-shockwave-flash" width="' . $video_width . '" height="' . $video_height . '" allowscriptaccess="always" allowfullscreen="true"></embed></object>',
            "youtube" => '<iframe width="' . $video_width . '" height="' . $video_height . '" src="http://www.youtube.com/embed/' . $video_url . '?autoplay=1&cc_load_policy=0&modestbranding=1&iv_load_policy=3" frameborder="0" allowfullscreen></iframe>',
            "zshare" => '<iframe src="http://www.zshare.net/videoplayer/player.php?SID=dl073&FID=' . $video_url . '&FN=ceprov.flv&iframewidth=' . $video_width . '&iframeheight=200&width=' . $video_width . '&height=250&H=70886430149e13aa" height="500" width="' . $video_width . '"  border=0 frameborder=0 scrolling=no></iframe>',
        );
        return $embeds[$video_src];
    }

    /**
     * @param int $id
     * @return null|Documentary
     */
    public function getDocumentaryById(int $id)
    {
        return $this->documentaryRepository->find($id);
    }

    /**
     * @param Category $category
     * @return Documentary[]|ArrayCollection
     */
    public function getDocumentariesByCategory(Category $category)
    {
        $criteria = new DocumentaryCriteria();
        $criteria->setCategory($category);
        $criteria->setLimit(20);

        $documentaries = $this->getDocumentariesByCriteria($criteria);
        return $documentaries;
    }

    /**
     * @param Documentary $documentary
     */
    public function updateViews(Documentary $documentary)
    {
        $documentary->incrementViews();
        $this->save($documentary, UpdateTimestamps::NO);
    }

    /**
     * @param Documentary $documentary
     */
    public function updateCommentCountForDocumentary(Documentary $documentary)
    {
        $count = 0;

        $comments = $documentary->getComments();
        foreach ($comments as $comment) {
            if ($comment->isPublished()) {
                $count++;
            }
        }

        $documentary->setCommentCount($count);
        $this->save($documentary, UpdateTimestamps::NO);
    }

    /**
     * @param Documentary $documentary
     */
    public function updateWatchlistCountForDocumentary(Documentary $documentary)
    {
        $count = 0;

        $watchlists = $documentary->getWatchlists();
        foreach ($watchlists as $watchlist) {
            $count++;
        }

        $documentary->setWatchlistCount($count);
        $this->save($documentary, UpdateTimestamps::NO);
    }

    /**
     * @return array
     */
    public function getYearsExtractedFromDocumentaries()
    {
        return $this->documentaryRepository->findYearsExtractedFromDocumentaries();
    }

    /**
     * @param Documentary $documentary
     * @param string $updateTimestamps
     * @param bool $sync
     */
    public function save(Documentary $documentary, string $updateTimestamps = UpdateTimestamps::YES, bool $sync = true)
    {
        if ($updateTimestamps === UpdateTimestamps::YES) {
            $currentDateTime = new \DateTime();

            if ($documentary->getCreatedAt() == null) {
                $documentary->setCreatedAt($currentDateTime);
            } else {
                $documentary->setUpdatedAt($currentDateTime);
            }
        }

        $this->documentaryRepository->save($documentary, $sync);
    }

    /**
     * @param Documentary $documentary
     */
    public function createMovie(Documentary $documentary)
    {
        $this->save($documentary);
        $this->triggerDocumentarySavedEvent($documentary);
    }
    /**
     * @param Documentary $documentary
     */
    public function editMovie(Documentary $documentary)
    {
        $this->save($documentary);
        $this->triggerDocumentarySavedEvent($documentary);
    }

    /**
     * @param Documentary $documentary
     */
    public function createSeries(Documentary $documentary)
    {
        $this->save($documentary);
        $this->triggerDocumentarySavedEvent($documentary);
    }

    /**
     * @param Documentary $documentary
     */
    public function editSeries(Documentary $documentary)
    {
        $this->save($documentary);
        $this->triggerDocumentarySavedEvent($documentary);
    }

    /**
     * @param Documentary $documentary
     */
    public function convertToSeries(Documentary $documentary)
    {
        $this->save($documentary);
        $this->triggerDocumentarySavedEvent($documentary);
    }

    /**
     * @param Documentary $documentary
     */
    public function createEpisode(Documentary $documentary)
    {
        $this->save($documentary);
        $this->triggerDocumentarySavedEvent($documentary);
    }

    public function flush()
    {
        $this->documentaryRepository->flush();
    }

    /**
     * @param Movie $movie
     * @param string $sync
     * @throws \Doctrine\ORM\ORMException
     */
    public function removeMovie(Movie $movie, string $sync = Sync::YES)
    {
        $this->movieRepository->remove($movie, $sync);
    }

    /**
     * @param Documentary $documentary
     */
    private function triggerDocumentarySavedEvent(Documentary $documentary)
    {
        $documentaryEvent = new DocumentaryEvent($documentary);
        $this->eventDispatcher->dispatch($documentaryEvent, DocumentaryEvents::DOCUMENTARY_SAVED);
    }
}