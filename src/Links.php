<?php

namespace TH\Docker;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Iterator;

class Links implements ArrayAccess, Countable, Iterator
{
    private $links;
    private $iterator;

    public function __construct(Array $links)
    {
        $this->links = $links;
        $this->iterator = new ArrayIterator($links);
    }

    public function offsetExists($alias) {
        return isset($this->links[strtoupper($alias)]);
    }

    public function offsetGet($alias) {
        return $this->links[strtoupper($alias)];
    }

    public function offsetSet($alias, $value) {
        throw new \Exception('\TH\Docker\Links is read only');
    }

    public function offsetUnset($alias) {
        throw new \Exception('\TH\Docker\Links is read only');
    }

    public function count()
    {
        return count($this->links);
    }

    public function current() {
        return $this->iterator->current();
    }

    public function key() {
        return $this->iterator->key();
    }

    public function next() {
        return $this->iterator->next();
    }

    public function rewind() {
        return $this->iterator->rewind();
    }

    public function valid() {
        return $this->iterator->valid();
    }

    public function groupBy($regexp)
    {
        $sections = [];
        foreach ($this as $name => $link) {
            list($section, $key) = $this->matchRegexp($name, $regexp);
            if ($section !== false) {
                $sections = $this->addToGroup($sections, $link, $section, $key);
            }
        }
        return $sections;
    }

    private function matchRegexp($name, $regexp)
    {
        if (preg_match($regexp, $name, $matches)) {
            return [
                array_key_exists('section', $matches) ? $matches['section'] : $matches[1],
                array_key_exists('key', $matches) ? $matches['key'] : null
            ];
        }
        return [false, false];
    }

    private function addToGroup(Array $sections, $link, $section, $key)
    {
        if (!array_key_exists($section, $sections)) {
            $sections[$section] = [];
        }
        if ($key !== null) {
            $sections[$section][$key] = $link;
        } else {
            $sections[$section][] = $link;
        }
        return $sections;
    }

    public static function buildFrom(Array $env)
    {
        return new static(array_reduce(array_keys($env), function($links, $name) use ($env) {
            if (preg_match('/^(?<alias>[A-Z0-9_\.]+)_NAME$/', $name, $matches) === 1 && array_key_exists("{$matches['alias']}_PORT", $env)) {
                $links[$matches['alias']] = Link::build($env, $matches['alias']);
            }
            return $links;
        }, []));
    }
}
