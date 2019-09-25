<?php

declare(strict_types=1);

/**
 * Stream iterator
 *
 * @copyright   Copryright (c) 2018 gyselroth GmbH (https://gyselroth.com)
 * @license     MIT https://opensource.org/licenses/MIT
 */

namespace StreamIterator;

use Closure;
use Countable;
use IteratorAggregate;
use Psr\Http\Message\StreamInterface;
use Traversable;

/**
 * Wraps an interator to iterate through and cast each entry to string via a callback.
 */
class StreamIterator implements StreamInterface
{
    /**
     * @var Traversable
     */
    private $iterator;

    /**
     * Current position in iterator.
     *
     * @var int
     */
    private $position = 0;

    /**
     * Stringify callback.
     *
     * @var Closure
     */
    private $stringify;

    protected $exception_handler;

    /**
     * Construct a stream instance using an iterator.
     *
     * If the iterator is an IteratorAggregate, pulls the inner iterator
     * and composes that instead, to ensure we have access to the various
     * iterator capabilities.
     */
    public function __construct(Traversable $iterator, Closure $stringify = null, Closure $exception_handler=null)
    {
        if ($iterator instanceof IteratorAggregate) {
            $iterator = $iterator->getIterator();
        }
        $this->iterator = $iterator;
        $this->stringify = $stringify;
        $this->exception_handler = $exception_handler;
    }

    /**
     * @return string
     */
    public function __toString()
    {
       try {
            if($this->position !== 0) {
                $this->iterator->rewind();
            }

            return $this->getContents();
        } catch(\Throwable $e) {
            if($this->exception_handler !== null) {
                return $this->exception_handler->call($this, $e);
            }

            throw $e;
        }
    }

    /**
     * No-op.
     */
    public function close()
    {
    }

    /**
     * @return null|Traversable
     */
    public function detach()
    {
        $iterator = $this->iterator;
        $this->iterator = null;

        return $iterator;
    }

    /**
     * @return null|int returns the size of the iterator, or null if unknown
     */
    public function getSize()
    {
        if ($this->iterator instanceof Countable) {
            return count($this->iterator);
        }

        return null;
    }

    /**
     * @return int Position of the iterator
     */
    public function tell()
    {
        return $this->position;
    }

    /**
     * End of File.
     *
     * @return bool
     */
    public function eof()
    {
        if ($this->iterator instanceof Countable) {
            return $this->position === count($this->iterator);
        }

        return !$this->iterator->valid();
    }

    /**
     * Check if seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        return true;
    }

    /**
     * Seek the iterator.
     *
     * @param int $offset Stream offset
     * @param int $whence ignored
     *
     * @return bool returns TRUE on success or FALSE on failure
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!is_int($offset) && !is_numeric($offset)) {
            return false;
        }

        $offset = (int) $offset;
        if ($offset < 0) {
            return false;
        }

        $key = $this->iterator->key();
        if (!is_int($key) && !is_numeric($key)) {
            $key = 0;
            $this->iterator->rewind();
        }

        if ($key >= $offset) {
            $key = 0;
            $this->iterator->rewind();
        }

        while ($this->iterator->valid() && $key < $offset) {
            $this->iterator->next();
            ++$key;
        }

        $this->position = $key;

        return true;
    }

    /**
     * @see seek()
     *
     * @return bool returns true on success or false on failure
     */
    public function rewind()
    {
        $this->iterator->rewind();
        $this->position = 0;

        return true;
    }

    /**
     * Non-writable.
     *
     * @return bool Always returns false
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * Non-writable.
     *
     * @param string $string the string that is to be written
     *
     * @return bool|int Always returns false
     */
    public function write($string)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * Read content from iterator with a lenght limit (number of entries).
     *
     * @param int $length Read up to $length items from the iterator
     *
     * @return string
     */
    public function read($length)
    {
        $index = 0;
        $contents = '';
        while ($this->iterator->valid() && $index < $length) {
            if ($this->stringify !== null) {
                $contents .= $this->stringify->call($this, $this->iterator->current());
            } else {
                $contents .= $this->iterator->current();
            }

            $this->iterator->next();
            ++$this->position;
            ++$index;
        }

        return $contents;
    }

    /**
     * @return string
     */
    public function getContents()
    {
        $contents = '';
        while ($this->iterator->valid()) {
            if ($this->stringify !== null) {
                $contents .= $this->stringify->call($this, $this->iterator->current());
            } else {
                $contents .= $this->iterator->current();
            }

            $this->iterator->next();
            ++$this->position;
        }

        return $contents;
    }

    /**
     * @param string $key specific metadata to retrieve
     *
     * @return null|array returns an empty array if no key is provided, and
     *                    null otherwise
     */
    public function getMetadata($key = null)
    {
        return ($key === null) ? [] : null;
    }
}
