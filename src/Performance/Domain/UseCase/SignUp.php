<?php

namespace Performance\Domain\UseCase;

use Performance\Domain\Author;
use Performance\Domain\AuthorRepository;
use League\Flysystem\Filesystem;

class SignUp
{
    const S3_BASE_URL = 'https://s3.eu-west-2.amazonaws.com/execice/';
    const MAX_PHOTO_FILE_SIZE = 1000000;
    /**
	 * @var AuthorRepository
	 */
	private $authorRepository;

    /**
     * @var Filesystem
     */
    private $fileSystem;

	public function __construct(
	    AuthorRepository $authorRepository,
        Filesystem $filesystem
    ) {
		$this->authorRepository = $authorRepository;
        $this->fileSystem = $filesystem;
    }

	public function execute($username, $password, $profilePicture = null) {
        $photo = isset($profilePicture) ? $this->imageUpload($profilePicture) : null;
		$author = Author::register($username, $password, $photo);
		$this->authorRepository->save($author);
	}

	// TODO Extract this functionality into a component
	private function imageUpload(\Symfony\Component\HttpFoundation\File\UploadedFile $file) {
        $this->checkIsValidPhotoFile($file);
        $filename = md5(microtime(true)) . '.' . $file->getClientOriginalExtension();
        $this->fileSystem->put(
            $filename,
            file_get_contents($file->getPathname())
        );

        return self::S3_BASE_URL . $filename;
    }

    // TODO Extract this functionality into a component
    private function checkIsValidPhotoFile(\Symfony\Component\HttpFoundation\File\UploadedFile $file) {
        if (!$this->isValidMimeType($file->getMimeType())) {
            throw new \Exception('Invalid Mime Type');
        }

        if (!$this->isValidPhotoSize($file->getSize())) {
            throw new \Exception('Invalid Photo Size');
        }
    }

    // TODO Extract this functionality into a component
    private function isValidMimeType($mimeType) {
        if ($mimeType === 'image/gif'){
            return true;
        }
        if ($mimeType === 'image/png'){
            return true;
        }
        if ($mimeType === 'image/jpeg'){
            return true;
        }

        return false;
    }

    // TODO Extract this functionality into a component
    private function isValidPhotoSize($size) {
        if($size < self::MAX_PHOTO_FILE_SIZE) {
            return true;
        }

        return false;
    }
}