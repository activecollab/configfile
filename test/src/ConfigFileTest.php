<?php

  namespace ActiveCollab\ConfigFile\Test;

  use ActiveCollab\ConfigFile\ConfigFile;

  /**
   * @package ActiveCollab\ConfigFile\Test
   */
  class ConfigFileTest extends TestCase
  {
    /**
     * @var ConfigFile
     */
    private $config_file;

    /**
     * Set up test environment
     */
    public function setUp()
    {
      parent::setUp();

      $this->config_file = new ConfigFile($this->examples_path . '/test.inc.php');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionOnInvalidFile()
    {
      new ConfigFile($this->examples_path . '/test.txt');
    }

    /**
     * Test if integer values are properly parsed
     */
    public function testParsingInteger()
    {
      $this->assertSame(1, $this->config_file->getOption('ONE'));
      $this->assertSame(2, $this->config_file->getOption('TWO'));
      $this->assertSame(3, $this->config_file->getOption('THREE'));
    }

    /**
     * Test if float values are properly paresed
     */
    public function testParsingFloat()
    {
      $this->assertSame(2.25, $this->config_file->getOption('FLOAT'));
    }

    /**
     * Test if single quote strings are properly parsed
     */
    public function testParsingSingleQuotedString()
    {
      $this->assertSame('single', $this->config_file->getOption('SINGLE_QUOTED_STRING'));
    }

    /**
     * Test if double quoted strings are properly parsed
     */
    public function testParsingDoubleQuotedString()
    {
      $this->assertSame('double', $this->config_file->getOption('DOUBLE_QUOTED_STRING'));
    }

    /**
     * Test if booleans are properly parsed
     */
    public function testParsingBoolean()
    {
      $this->assertSame(true, $this->config_file->getOption('THIS_IS_TRUE'));
      $this->assertSame(false, $this->config_file->getOption('THIS_IS_FALSE'));
    }

    /**
     * Test if config file ignores declarations in comments
     */
    public function testIgnoredDeclarationsInComments()
    {
      $this->assertFalse($this->config_file->optionExists('IGNORE_ME'));
      $this->assertFalse($this->config_file->optionExists('THIS_SHOULD_BE_IGNORED'));
    }
  }
