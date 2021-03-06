<?php

class Database {
	
	private $db_host = 'localhost';
	private $db_name = 'body';
	private $db_user = 'root';
	private $db_pass = '';

	private $db ='';

	private function openConnexion() 
	{
		$this->db = new MySQLi( $this->db_host, $this->db_user, $this->db_pass, $this->db_name);

	}

	private function closeConnexion() 
	{
		$this->db->close();
	}

	public function checkUsernameAvailability( $username )
	{
		$this->openConnexion();

		$stmt = $this->db->prepare('SELECT username FROM t_users WHERE username  LIKE ?');
		$stmt->bind_param('s', $username);
		$stmt->execute();
		$stmt->bind_result($user_name);
		$stmt->store_result();
		
		$num_results = $stmt->num_rows;
		
		$stmt->close();
		$this->closeConnexion();
			
		return $num_results;
	}

	public function checkEmailAvailability( $email )
	{
		$this->openConnexion();

		$stmt = $this->db->prepare('SELECT email FROM t_users WHERE email LIKE ?'); 
		$stmt->bind_param('s', $email);
		$stmt->execute();
		$stmt->bind_result($user_email);
		$stmt->store_result();

		$num_results = $stmt->num_rows;

		$stmt->close();
		$this->closeConnexion();
			
		return $num_results;
	}

	public function addUser( $username, $password, $email, $localisation)
	{
		//sécuriser le mdp avec un hash);

		$this->openConnexion();
		
		$stmt = $this->db->prepare('INSERT INTO t_users (username, password, email, localisation) VALUES (?,?,?,?)');
		$stmt->bind_param('sssi', $username, $password, $email, $localisation);
		$stmt->execute();
		$stmt->close();
		
		$stmt = $this->db->prepare('SELECT username FROM t_users WHERE username LIKE ?');
		$stmt->bind_param('s', $username);
		$stmt->execute();
		$stmt->bind_result($user_name);
		$stmt->store_result();
		
		$num_results = $stmt->num_rows;
		
		if( $num_results > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
		
		$stmt->close();		
		$this->closeConnexion();
		
	}

	public function userLogin ( $username, $password )
    {
	    $this->openConnexion();

        $stmt = $this->db->prepare('SELECT username, email, role FROM t_users WHERE username LIKE ? AND password LIKE ?');
        $stmt->bind_param('ss', $username, $password);
        $stmt->execute();
        $stmt->bind_result($dbUsername, $dbEmail, $dbRole);
        $stmt->store_result();

        if ($stmt->fetch()) {
            $_SESSION['username'] = $dbUsername;
            $_SESSION['email'] = $dbEmail;
            $_SESSION['role'] = $dbRole;
            $_SESSION['logged'] = true;

            return true;
        }
        else {
            return false;
        }
    }

    public function getLocalisation( $cp )
    {

        $this->openConnexion();

        $stmt = $this->db->prepare('SELECT id, ville FROM t_localisation WHERE code_postal = ?');
        $stmt->bind_param('i', $cp);
        $stmt->execute();
        $stmt->bind_result($ville_id, $ville_name);

        $r = array();

        while($stmt->fetch())
        {
            $r[]= Array(
                'id' => $ville_id,
                'name' => utf8_encode($ville_name)
            );
        }

        $stmt->close();
        $this->closeConnexion();

        return json_encode($r);
    }
}

?>