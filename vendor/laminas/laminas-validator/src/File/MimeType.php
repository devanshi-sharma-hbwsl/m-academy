<?php

namespace Laminas\Validator\File;

use finfo;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\ErrorHandler;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;
use Psr\Http\Message\UploadedFileInterface;
use Traversable;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_unique;
use function class_exists;
use function explode;
use function finfo_file;
use function finfo_open;
use function getenv;
use function implode;
use function in_array;
use function is_array;
use function is_file;
use function is_int;
use function is_readable;
use function is_string;
use function sprintf;
use function trim;

use const E_NOTICE;
use const E_WARNING;
use const FILEINFO_MIME_TYPE;

/**
 * Validator for the mime type of a file
 */
class MimeType extends AbstractValidator
{
    use FileInformationTrait;

    /**#@+
     *
     * @const Error type constants
     */
    public const FALSE_TYPE   = 'fileMimeTypeFalse';
    public const NOT_DETECTED = 'fileMimeTypeNotDetected';
    public const NOT_READABLE = 'fileMimeTypeNotReadable';
    /**#@-*/

    /** @var array<string, string> */
    protected $messageTemplates = [
        self::FALSE_TYPE   => "File has an incorrect mimetype of '%type%'",
        self::NOT_DETECTED => 'The mimetype could not be detected from the file',
        self::NOT_READABLE => 'File is not readable or does not exist',
    ];

    /** @var array */
    protected $messageVariables = [
        'type' => 'type',
    ];

    /** @var string */
    protected $type;

    /**
     * Finfo object to use
     *
     * @var finfo|null
     */
    protected $finfo;

    /**
     * If no environment variable 'MAGIC' is set, try and autodiscover it based on common locations
     *
     * @var list<non-empty-string>
     */
    protected $magicFiles = [
        '/usr/share/misc/magic',
        '/usr/share/misc/magic.mime',
        '/usr/share/misc/magic.mgc',
        '/usr/share/mime/magic',
        '/usr/share/mime/magic.mime',
        '/usr/share/mime/magic.mgc',
        '/usr/share/file/magic',
        '/usr/share/file/magic.mime',
        '/usr/share/file/magic.mgc',
    ];

    /**
     * Options for this validator
     *
     * @var array
     */
    protected $options = [
        'enableHeaderCheck' => false, // Allow header check
        'disableMagicFile'  => false, // Disable usage of magicfile
        'magicFile'         => null, // Magicfile to use
        'mimeType'          => null, // Mimetype to allow
    ];

    /**
     * Sets validator options
     *
     * Mimetype to accept
     * - NULL means default PHP usage by using the environment variable 'magic'
     * - FALSE means disabling searching for mimetype, should be used for PHP 5.3
     * - A string is the mimetype file to use
     *
     * @param  string|array|Traversable $options
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (is_string($options)) {
            $this->setMimeType($options);
            $options = [];
        } elseif (is_array($options)) {
            if (isset($options['magicFile'])) {
                $this->setMagicFile($options['magicFile']);
                unset($options['magicFile']);
            }

            if (isset($options['enableHeaderCheck'])) {
                $this->enableHeaderCheck((bool) $options['enableHeaderCheck']);
                unset($options['enableHeaderCheck']);
            }

            if (array_key_exists('mimeType', $options)) {
                $this->setMimeType($options['mimeType']);
                unset($options['mimeType']);
            }

            // Handle cases where mimetypes are interspersed with options, or
            // options are simply an array of mime types
            foreach (array_keys($options) as $key) {
                if (! is_int($key)) {
                    continue;
                }
                $this->addMimeType($options[$key]);
                unset($options[$key]);
            }
        }

        parent::__construct($options);
    }

    /**
     * Returns the actual set magicfile
     *
     * @deprecated Since 2.61.0 - All option getters and setters will be removed in 3.0
     *
     * @return string|false
     */
    public function getMagicFile()
    {
        if (null === $this->options['magicFile']) {
            $magic = getenv('magic');
            if (is_string($magic) && $magic !== '') {
                $this->setMagicFile($magic);
                if ($this->options['magicFile'] === null) {
                    $this->options['magicFile'] = false;
                }
                return $this->options['magicFile'];
            }

            foreach ($this->magicFiles as $file) {
                try {
                    $this->setMagicFile($file);
                } catch (Exception\ExceptionInterface) {
                    // suppressing errors which are thrown due to open_basedir restrictions
                    continue;
                }

                if (is_string($this->options['magicFile'])) {
                    return $this->options['magicFile'];
                }
            }

            if ($this->options['magicFile'] === null) {
                $this->options['magicFile'] = false;
            }
        }

        return $this->options['magicFile'];
    }

    /**
     * Sets the magicfile to use
     * if null, the MAGIC constant from php is used
     * if the MAGIC file is erroneous, no file will be set
     * if false, the default MAGIC file from PHP will be used
     *
     * @deprecated Since 2.61.0 - All option getters and setters will be removed in 3.0
     *
     * @param string $file
     * @return self Provides fluid interface
     * @throws Exception\InvalidArgumentException
     * @throws Exception\InvalidMagicMimeFileException
     * @throws Exception\RuntimeException When finfo can not read the magicfile.
     */
    public function setMagicFile($file)
    {
        if ($file === false) {
            $this->options['magicFile'] = false;
        } elseif (empty($file)) {
            $this->options['magicFile'] = null;
        } elseif (! class_exists('finfo', false)) {
            $this->options['magicFile'] = null;
            throw new Exception\RuntimeException('Magicfile can not be set; there is no finfo extension installed');
        } elseif (! is_file($file) || ! is_readable($file)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The given magicfile ("%s") could not be read',
                $file
            ));
        } else {
            ErrorHandler::start(E_NOTICE | E_WARNING);
            $this->finfo = finfo_open(FILEINFO_MIME_TYPE, $file);
            $error       = ErrorHandler::stop();
            if (empty($this->finfo)) {
                $this->finfo = null;
                throw new Exception\InvalidMagicMimeFileException(sprintf(
                    'The given magicfile ("%s") could not be used by ext/finfo',
                    $file
                ), 0, $error);
            }
            $this->options['magicFile'] = $file;
        }

        return $this;
    }

    /**
     * Disables usage of MagicFile
     *
     * @deprecated Since 2.61.0 - All option getters and setters will be removed in 3.0
     *
     * @param bool $disable False disables usage of magic file; true enables it.
     * @return self Provides fluid interface
     */
    public function disableMagicFile($disable)
    {
        $this->options['disableMagicFile'] = (bool) $disable;
        return $this;
    }

    /**
     * Is usage of MagicFile disabled?
     *
     * @deprecated Since 2.61.0 - All option getters and setters will be removed in 3.0
     *
     * @return bool
     */
    public function isMagicFileDisabled()
    {
        return $this->options['disableMagicFile'];
    }

    /**
     * Returns the Header Check option
     *
     * @deprecated Since 2.61.0 - All option getters and setters will be removed in 3.0
     *
     * @return bool
     */
    public function getHeaderCheck()
    {
        return $this->options['enableHeaderCheck'];
    }

    /**
     * Defines if the http header should be used
     * Note that this is unsafe and therefor the default value is false
     *
     * @deprecated Since 2.61.0 - All option getters and setters will be removed in 3.0
     *
     * @param bool $headerCheck
     * @return self Provides fluid interface
     */
    public function enableHeaderCheck($headerCheck = true)
    {
        $this->options['enableHeaderCheck'] = (bool) $headerCheck;
        return $this;
    }

    /**
     * Returns the set mimetypes
     *
     * @deprecated Since 2.61.0 - All option getters and setters will be removed in 3.0
     *
     * @param bool $asArray Returns the values as array, when false a concatenated string is returned
     * @return string|list<string>
     * @psalm-return ($asArray is true ? list<string> : string)
     */
    public function getMimeType($asArray = false)
    {
        $asArray  = (bool) $asArray;
        $mimetype = (string) $this->options['mimeType'];
        if ($asArray) {
            $mimetype = explode(',', $mimetype);
        }

        return $mimetype;
    }

    /**
     * Sets the mimetypes
     *
     * @deprecated Since 2.61.0 - All option getters and setters will be removed in 3.0
     *
     * @param string|list<string> $mimetype The mimetypes to validate
     * @return self Provides a fluent interface
     */
    public function setMimeType($mimetype)
    {
        $this->options['mimeType'] = null;
        $this->addMimeType($mimetype);
        return $this;
    }

    /**
     * Adds the mimetypes
     *
     * @deprecated Since 2.61.0 - All option getters and setters will be removed in 3.0
     *
     * @param  string|list<string> $mimetype The mimetypes to add for validation
     * @throws Exception\InvalidArgumentException
     * @return self Provides a fluent interface
     */
    public function addMimeType($mimetype)
    {
        $mimetypes = $this->getMimeType(true);

        if (is_string($mimetype)) {
            $mimetype = explode(',', $mimetype);
        } elseif (! is_array($mimetype)) {
            throw new Exception\InvalidArgumentException('Invalid options to validator provided');
        }

        if (isset($mimetype['magicFile'])) {
            unset($mimetype['magicFile']);
        }

        foreach ($mimetype as $content) {
            if (! is_string($content) || $content === '') {
                continue;
            }

            $mimetypes[] = trim($content);
        }
        $mimetypes = array_unique(array_filter($mimetypes));

        $this->options['mimeType'] = implode(',', $mimetypes);

        return $this;
    }

    /**
     * Defined by Laminas\Validator\ValidatorInterface
     *
     * Returns true if the mimetype of the file matches the given ones. Also parts
     * of mimetypes can be checked. If you give for example "image" all image
     * mime types will be accepted like "image/gif", "image/jpeg" and so on.
     *
     * @param  string|array|UploadedFileInterface $value Real file to check for mimetype
     * @param  array               $file  File data from \Laminas\File\Transfer\Transfer (optional)
     * @return bool
     */
    public function isValid($value, $file = null)
    {
        $fileInfo = $this->getFileInfo($value, $file, true);

        $this->setValue($fileInfo['filename']);

        // Is file readable ?
        if (empty($fileInfo['file']) || false === is_readable($fileInfo['file'])) {
            $this->error(static::NOT_READABLE);
            return false;
        }

        $mimefile = $this->getMagicFile();
        if (class_exists('finfo', false)) {
            if (! $this->isMagicFileDisabled() && (is_string($mimefile) && empty($this->finfo))) {
                ErrorHandler::start(E_NOTICE | E_WARNING);
                $this->finfo = finfo_open(FILEINFO_MIME_TYPE, $mimefile);
                ErrorHandler::stop();
            }

            if (empty($this->finfo)) {
                ErrorHandler::start(E_NOTICE | E_WARNING);
                $this->finfo = finfo_open(FILEINFO_MIME_TYPE);
                ErrorHandler::stop();
            }

            $this->type = null;
            if (! empty($this->finfo)) {
                $this->type = finfo_file($this->finfo, $fileInfo['file']);
                unset($this->finfo);
            }
        }

        if ($this->type === null && $this->getHeaderCheck()) {
            $this->type = $fileInfo['filetype'];
        }

        if ($this->type === null) {
            $this->error(static::NOT_DETECTED);
            return false;
        }

        $mimetype = $this->getMimeType(true);
        if (in_array($this->type, $mimetype)) {
            return true;
        }

        $types = explode('/', $this->type);
        $types = array_merge($types, explode('-', $this->type));
        $types = array_merge($types, explode(';', $this->type));
        foreach ($mimetype as $mime) {
            if (in_array($mime, $types)) {
                return true;
            }
        }

        $this->error(static::FALSE_TYPE);
        return false;
    }
}
