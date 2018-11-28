<?php
declare(strict_types=1);

namespace WoohooLabs\Yang\JsonApi\Schema\Link;

use WoohooLabs\Yang\JsonApi\Exception\LinkException;

final class DocumentLinks
{
    /**
     * @var Link[]
     */
    private $links;

    /**
     * @param Link[] $links
     */
    public function __construct(array $links)
    {
        $this->links = $links;
    }

    public function hasSelf(): bool
    {
        return $this->hasLink("self");
    }

    public function self(): Link
    {
        return $this->link("self");
    }

    public function hasRelated(): bool
    {
        return $this->hasLink("related");
    }

    public function related(): Link
    {
        return $this->link("related");
    }

    public function hasFirst(): bool
    {
        return $this->hasLink("first");
    }

    public function first(): Link
    {
        return $this->link("first");
    }

    public function hasLast(): bool
    {
        return $this->hasLink("last");
    }

    public function last(): Link
    {
        return $this->link("last");
    }

    public function hasPrev(): bool
    {
        return $this->hasLink("prev");
    }

    public function prev(): Link
    {
        return $this->link("prev");
    }

    public function hasNext(): bool
    {
        return $this->hasLink("next");
    }

    public function next(): Link
    {
        return $this->link("next");
    }

    public function hasAbout(): bool
    {
        return $this->hasLink("about");
    }

    public function about(): Link
    {
        return $this->link("about");
    }

    public function hasLink(string $name): bool
    {
        return isset($this->links[$name]);
    }

    public function link(string $name): Link
    {
        if (isset($this->links[$name]) === false) {
            throw new LinkException("Link with '$name rel type cannot be found!");
        }

        return $this->links[$name];
    }

    public function hasAnyLinks(): bool
    {
        return empty($this->links) === false;
    }

    /**
     * @return Link[]
     */
    public function links(): array
    {
        return $this->links;
    }

    /**
     * @internal
     */
    public static function fromArray(array $links): Links
    {
        $linkObjects = [];
        foreach ($links as $name => $value) {
            if (is_string($value)) {
                $linkObjects[$name] = Link::createFromString($value);
            } elseif (is_array($value)) {
                $linkObjects[$name] = Link::fromArray($value);
            }
        }

        return new self($linkObjects);
    }

    public function toArray(): array
    {
        $links = [];

        foreach ($this->links as $rel => $link) {
            $links[$rel] = $link->toArray();
        }

        return $links;
    }
}
