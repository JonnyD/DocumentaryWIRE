<?php

namespace App\Criteria;

use App\Entity\Category;
use App\Entity\VideoSource;

class DocumentaryCriteria
{
    /**
     * @var bool
     */
    private $featured;

    /**
     * @var string
     */
    private $status;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var VideoSource
     */
    private $videoSource;

    /**
     * @var int
     */
    private $year;

    /**
     * @var string
     */
    private $duration;

    /**
     * @var array
     */
    private $sort;

    /**
     * @var int
     */
    private $limit;

    /**
     * @return boolean
     */
    public function isFeatured()
    {
        return $this->featured;
    }

    /**
     * @param boolean $featured
     */
    public function setFeatured(bool $featured)
    {
        $this->featured = $featured;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    /**
     * @return VideoSource
     */
    public function getVideoSource()
    {
        return $this->videoSource;
    }

    /**
     * @param VideoSource $videoSource
     */
    public function setVideoSource(VideoSource $videoSource)
    {
        $this->videoSource = $videoSource;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear(int $year)
    {
        $this->year = $year;
    }

    /**
     * @return string
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param string $duration
     */
    public function setDuration(string $duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return array
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param array $sort
     */
    public function setSort(array $sort)
    {
        $this->sort = $sort;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;
    }
}