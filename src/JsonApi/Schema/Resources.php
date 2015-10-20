<?php
namespace WoohooLabs\Yang\JsonApi\Schema;

class Resources
{
    /*
     * @var bool
     */
    protected $isSinglePrimaryResource;

    /**
     * @var \WoohooLabs\Yang\JsonApi\Schema\Resource[]
     */
    protected $resources = [];

    /**
     * @var array
     */
    protected $primaryKeys = [];

    /**
     * @var array
     */
    protected $includedKeys = [];

    /**
     * @param array $data
     * @param array $included
     */
    public function __construct(array $data, array $included)
    {
        $this->isSinglePrimaryResource = $this->isAssociativeArray($data);

        if ($this->isSinglePrimaryResource) {
            $this->addPrimaryResource(new Resource($data, $this));
        } else {
            foreach ($data as $resource) {
                $this->addPrimaryResource(new Resource($resource, $this));
            }
        }

        foreach ($included as $resource) {
            $this->addIncludedResource(new Resource($resource, $this));
        }
    }

    /**
     * @return array|null
     */
    public function primaryDataToArray()
    {
        return $this->isSinglePrimaryResource ? $this->primaryResourceToArray() : $this->primaryCollectionToArray();
    }

    /**
     * @return array
     */
    public function includedToArray()
    {
        ksort($this->includedKeys);

        $result = [];
        foreach ($this->includedKeys as $type => $ids) {
            ksort($ids);
            foreach ($ids as $id => $value) {
                $result[] = $this->resources[$type][$id]->toArray();
            }
        }

        return $result;
    }

    /**
     * @return array|null
     */
    protected function primaryResourceToArray()
    {
        if ($this->hasPrimaryResources() === false) {
            return null;
        }

        $ids = reset($this->primaryKeys);
        $key = key($this->primaryKeys);
        $id = key($ids);

        return $this->resources[$key][$id]->toArray();
    }

    /**
     * @return array
     */
    protected function primaryCollectionToArray()
    {
        ksort($this->primaryKeys);

        $result = [];
        foreach ($this->primaryKeys as $type => $ids) {
            ksort($ids);
            foreach ($ids as $id => $value) {
                $result[] = $this->resources[$type][$id]->toArray();
            }
        }

        return $result;
    }

    /**
     * @param string $type
     * @param string $id
     * @return \WoohooLabs\Yang\JsonApi\Schema\Resource|null
     */
    public function getResource($type, $id)
    {
        return isset($this->resources[$type][$id]) ? $this->resources[$type][$id] : null;
    }

    /**
     * @return bool
     */
    public function hasPrimaryResources()
    {
        return empty($this->primaryKeys) === false;
    }

    /**
     * @return \WoohooLabs\Yang\JsonApi\Schema\Resource[]
     */
    public function getPrimaryResources()
    {
        $resources = [];
        foreach ($this->primaryKeys as $type => $ids) {
            foreach ($ids as $id) {
                $resources[] = $this->getResource($type, $id);
            }
        }

        return $resources;
    }

    /**
     * @return \WoohooLabs\Yang\JsonApi\Schema\Resource|null
     */
    public function getPrimaryResource()
    {
        $ids = reset($this->primaryKeys);
        $type = key($this->primaryKeys);
        $id = key($ids);

        return $this->getResource($type, $id);
    }

    /**
     * @param string $type
     * @param string $id
     * @return bool
     */
    public function hasPrimaryResource($type, $id)
    {
        return isset($this->primaryKeys[$type][$id]);
    }

    /**
     * @return bool
     */
    public function hasIncludedResources()
    {
        return empty($this->includedKeys) === false;
    }

    /**
     * @param string $type
     * @param string $id
     * @return bool
     */
    public function hasIncludedResource($type, $id)
    {
        return isset($this->includedKeys[$type][$id]);
    }

    /**
     * @return \WoohooLabs\Yang\JsonApi\Schema\Resource[]
     */
    public function getIncludedResources()
    {
        $resources = [];
        foreach ($this->includedKeys as $type => $ids) {
            foreach ($ids as $id) {
                $resources[] = $this->getResource($type, $id);
            }
        }

        return $resources;
    }

    /**
     * @param \WoohooLabs\Yang\JsonApi\Schema\Resource $resource
     * @return $this
     */
    protected function addPrimaryResource(Resource $resource)
    {
        $type = $resource->type();
        $id = $resource->id();
        if ($this->hasIncludedResource($type, $id) === true) {
            unset($this->includedKeys[$type][$id]);
            $this->primaryKeys[$type][$id] = true;
        } else {
            $this->addResource($this->primaryKeys, $resource);
        }

        return $this;
    }

    /**
     * @param \WoohooLabs\Yang\JsonApi\Schema\Resource $resource
     * @return $this
     */
    protected function addIncludedResource(Resource $resource)
    {
        if ($this->hasPrimaryResource($resource->type(), $resource->id()) === false) {
            $this->addResource($this->includedKeys, $resource);
        }

        return $this;
    }

    protected function addResource(&$keys, Resource $resource)
    {
        $type = $resource->type();
        $id = $resource->id();

        $keys[$type][$id] = true;
        $this->resources[$type][$id] = $resource;
    }

    /**
     * @param array $array
     * @return bool
     */
    private function isAssociativeArray(array $array)
    {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }
}
