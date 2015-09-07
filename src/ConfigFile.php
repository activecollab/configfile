<?php
namespace ActiveCollab\ConfigFile;

use InvalidArgumentException;

/**
 * @package ActiveCollab\ConfigFile
 */
class ConfigFile
{
    /**
     * @var string
     */
    private $file_path;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $file_path
     */
    public function __construct($file_path)
    {
        if (is_file($file_path) && in_array(pathinfo($file_path, PATHINFO_EXTENSION), ['php', 'inc'])) {
            $this->file_path = $file_path;
            $this->options = $this->fileWithConstantsToArray($file_path);
        } else {
            throw new InvalidArgumentException('PHP configuration file not found at ' . $file_path);
        }
    }

    /**
     * Return all options that we found in the file
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Return TRUE if option exists in the loaded file
     *
     * @param  string $name
     * @return bool
     */
    public function optionExists($name)
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * Return a single option
     *
     * If option is not set, NULL is returned. To check if option exists, use ConfigFile::optionExists() method
     *
     * @param  string $name
     * @return mixed
     */
    public function getOption($name)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : null;
    }

    // ---------------------------------------------------
    //  Parsing
    // ---------------------------------------------------

    /**
     * Get constants from the given PHP file (these constants need to be defined with const keyword)
     *
     * @param  string $file_path
     * @return array
     */
    private function fileWithConstantsToArray($file_path)
    {
        $result = [];

        $lines = file($file_path);

        if (is_array($lines)) {
            foreach ($lines as $line) {
                $line = trim(trim($line, '<?php'));

                if ($this->strStartsWith($line, 'const ')) {
                    list ($constant_name, $value) = $this->getFromConst($line);

                    $result[$constant_name] = $value;
                } else {
                    if ($this->strContains($line, 'define')) {
                        foreach ($this->getFromDefine($line) as $option) {
                            list ($constant_name, $value) = $option;

                            $result[$constant_name] = $value;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Return single option from const DB_XYZ defition line
     *
     * @param  string $line
     * @return string
     */
    private function getFromConst($line)
    {
        $eq_pos = strpos($line, '=');
        $semicolon_pos = strrpos($line, ';');

        $constant_name = trim(substr($line, 6, $eq_pos - 6));
        $value = trim(substr($line, $eq_pos + 1, $semicolon_pos - $eq_pos - 1));

        return [$constant_name, $this->getNativeValueFromDefinition($value)];
    }

    /**
     * Return single option from define('DB_XYZ', value) defition line
     *
     * Adopted from:
     *
     * http://stackoverflow.com/questions/645862/regex-to-parse-define-contents-possible
     *
     * @param  string $line
     * @return array
     */
    function getFromDefine($line)
    {
        $line = "<?php $line"; // Trick the parser that we are in PHP file, instead of analyzing a single line

        $state = 0;
        $key = $value = '';

        $tokens = token_get_all($line);
        $token = reset($tokens);

        while ($token) {
            if (is_array($token)) {
                if ($token[0] == T_WHITESPACE || $token[0] == T_COMMENT || $token[0] == T_DOC_COMMENT) {
                    // do nothing
                } else {
                    if ($token[0] == T_STRING && strtolower($token[1]) == 'define') {
                        $state = 1;
                    } else {
                        if ($state == 2 && $this->isConstantToken($token[0])) {
                            $key = $token[1];
                            $state = 3;
                        } else {
                            if ($state == 4 && $this->isConstantToken($token[0])) {
                                $value = $token[1];
                                $state = 5;
                            }
                        }
                    }
                }
            } else {
                $symbol = trim($token);
                if ($symbol == '(' && $state == 1) {
                    $state = 2;
                } else {
                    if ($symbol == ',' && $state == 3) {
                        $state = 4;
                    } else {
                        if ($symbol == ')' && $state == 5) {
                            $state = 0;

                            yield [$this->stripQuotes($key), $this->getNativeValueFromDefinition($value)];
                        }
                    }
                }
            }
            $token = next($tokens);
        }
    }

    /**
     * Return config option name from defintiion string
     *
     * @param  string $constant_name
     * @return string
     */
    public function getOptionNameFromDefinition($constant_name)
    {
        if ($this->strStartsWith($constant_name, "'") && $this->strEndsWith($constant_name, "'")) {
            return trim(trim($constant_name, "'")); // single quote
        } else {
            if ($this->strStartsWith($constant_name, '"') && $this->strEndsWith($constant_name, '"')) {
                return trim(trim($constant_name, '"')); // double quote
            } else {
                return $constant_name;
            }
        }
    }

    /**
     * Cast declared value to internal type
     *
     * @param  string $value
     * @return mixed
     */
    private function getNativeValueFromDefinition($value)
    {
        if ($this->strStartsWith($value, "'") && $this->strEndsWith($value, "'")) {
            $value = trim(trim($value, "'")); // single quote
        } else {
            if ($this->strStartsWith($value, '"') && $this->strEndsWith($value, '"')) {
                $value = trim(trim($value, '"')); // double quote
            } else {
                if ($value == 'true') {
                    $value = true;
                } else {
                    if ($value == 'false') {
                        $value = false;
                    } else {
                        if (is_numeric($value)) {
                            if (ctype_digit($value)) {
                                return (integer)$value;
                            } else {
                                return (float)$value;
                            }
                        }
                    }
                }
            }
        }

        return $value;
    }

    /**
     * Return true if $token is constant token
     *
     * @param  string $token
     * @return bool
     */
    private function isConstantToken($token)
    {
        return $token == T_CONSTANT_ENCAPSED_STRING || $token == T_STRING || $token == T_LNUMBER || $token == T_DNUMBER;
    }

    /**
     * Strip single and double quotes
     *
     * @param  string $value
     * @return string
     */
    private function stripQuotes($value)
    {
        return preg_replace('!^([\'"])(.*)\1$!', '$2', $value);
    }

    /**
     * Case insensitive string begins with
     *
     * @param string $string
     * @param string $niddle
     * @return boolean
     */
    private function strStartsWith($string, $niddle)
    {
        return mb_strtolower(substr($string, 0, mb_strlen($niddle))) == mb_strtolower($niddle);
    }

    /**
     * Case insensitive string contains
     *
     * @param  string $string
     * @param  string $niddle
     * @return bool
     */
    private function strContains($string, $niddle)
    {
        return mb_strpos(mb_strtolower($string), mb_strtolower($niddle)) !== false;
    }

    /**
     * Case insensitive string ends with
     *
     * @param  string $string
     * @param  string $niddle
     * @return boolean
     */
    private function strEndsWith($string, $niddle)
    {
        return mb_substr($string, mb_strlen($string) - mb_strlen($niddle), mb_strlen($niddle)) == $niddle;
    }
}