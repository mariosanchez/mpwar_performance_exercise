<?php

namespace Performance\Domain;

class Author
{
	private $id;
	private $username;
	private $password;
	private $photo;

	public static function register($username, $password, $photo) {
		$author = new Author();
		$author->username = $username;
		$author->password = password_hash($password, PASSWORD_DEFAULT);
		$author->photo = $photo;

		return $author;
	}

	public static function fromArray($authorArray) {
		$author = new Author();
		$author->id = $authorArray['id'];
		$author->username = $authorArray['username'];
		$author->password = $authorArray['password'];
		$author->photo = $authorArray['photo'];

		return $author;
	}

	public function verifyPassword($plainTextPassword) {
		return password_verify($plainTextPassword, $this->password);
	}

	public function getId() {
		return $this->id;
	}

	public function getUsername() {
		return $this->username;
	}

	public function getPhoto() {
        return $this->photo;
    }
}