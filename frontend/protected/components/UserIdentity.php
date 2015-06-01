<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
    private $_id;
    private $_active;
    private $_first_name;
    private $_last_name;

	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */

    public function authenticate() {
        $username = $this->username;
        $user = Accounts::model()->find( "username = '{$username}'");

        if ($user === null) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        } else {
            $date = date_format(date_create($user->created), "d-m-Y");
            if ($user->password != md5($this->password . $date)) {
                $this->errorCode = self::ERROR_PASSWORD_INVALID;
            }
            else {
                $this->_id = $user->id;
                $this->_active = $user->active;
                $this->_first_name = $user->first_name;
                $this->_last_name = $user->last_name;

                $this->errorCode = self::ERROR_NONE;
            }
        }
        return $this->errorCode == self::ERROR_NONE;

    }
    public function getId() {
        return $this->_id;
    }
    public function getActive() {
        return $this->_active;
    }
    public function getFirstname() {
        return $this->_first_name;
    }
    public function getLastname() {
        return $this->_last_name;
    }

}