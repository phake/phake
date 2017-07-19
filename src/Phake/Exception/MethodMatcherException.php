<?php

namespace Phake\Exception;

/**
 * Thrown when a method call doesn't match an expection
 */
class MethodMatcherException extends \Exception
{
    private $argument;

    /**
     * @param string $message
     * @param Exception $previous
     */
    public function __construct($message = "", \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->argument = 0;
    }

    /**
     * Updates the argument position (used in the argument chain)
     */
    public function incrementArgumentPosition()
    {
        $this->argument++;
    }

    /**
     * Returns the argument's position (0 indexed)
     * @return int
     */
    public function getArgumentPosition()
    {
        return $this->argument;
    }

    /**
     * Get the message, but include the comparison diff.
     *
     * @internal This is so we can lazy generate the comparison message.
     * @return string
     */
    public function getMessageWithComparisonDiff()
    {
        $previous = $this;

        while($previous = $previous->getPrevious()) {
            if ($previous instanceof \SebastianBergmann\Comparator\ComparisonFailure) {
                return trim($this->getMessage() . "\n" . $previous->getDiff());
            }
        }

        return $this->getMessage();
    }
}
