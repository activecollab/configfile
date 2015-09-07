<?php
namespace ActiveCollab\ConfigFile\Test;

/**
 * @package ActiveCollab\JobsQueue\Test
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $examples_path;

    /**
     * Constructs a test case with the given name.
     *
     * @param string $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->examples_path = dirname(__DIR__) . '/examples';
    }
}