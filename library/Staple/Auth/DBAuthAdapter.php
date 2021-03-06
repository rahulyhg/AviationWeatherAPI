<?php
/** 
 * This is the packaged database authorization adapter. This adapter requires the following
 * settings to be included in the configuration file:
 *
 * adapter - Tells the Staple_Main class which AuthAdapter to load.
 * authtable - Specifies the database table where auth credentials reside.
 * uidfield - Defines the username or user identifer field.
 * pwfield - Defines the password field.
 * pwenctype - The type of encryption used on the password. Values include 'SHA256', 'Default'
 * rolefield - (optional) This field specifies the database table that holds the access level. If no field is provided or it is null, 1 will be returned.
 * 
 * @author Ironpilot
 * @copyright Copyright (c) 2011, STAPLE CODE
 * 
 * This file is part of the STAPLE Framework.
 * 
 * The STAPLE Framework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by the 
 * Free Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 * 
 * The STAPLE Framework is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License for 
 * more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with the STAPLE Framework.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */
namespace Staple\Auth;

use Staple\Config;
use Staple\Query\Query;

class DBAuthAdapter implements AuthAdapter
{
	use AuthRoute;
	/**
	 * Store the user identifier. Usually the username.
	 * @var string
	 */
	private $uid;
	
	/**
	 * getAuth checks the database for valid credentials and returns true if they are found.
	 * @param array $cred
	 * @return bool
	 * @see Staple_AuthAdapter::getAuth()
	 */
	public function getAuth($cred): bool
	{
		if(array_key_exists('username', $cred) AND array_key_exists('password', $cred))
		{
			switch(Config::getValue('DBAuthAdapter','pwenctype', false))
			{
				case 'SHA256':
					$pass = hash('sha256', $cred['password']);
					break;
				default:
					$pass = password_hash($cred['password'], PASSWORD_DEFAULT);
			}

			$columns = [
				Config::getValue('DBAuthAdapter','uidfield'),
				Config::getValue('DBAuthAdapter','pwfield')
			];
			$query = Query::select(Config::getValue('DBAuthAdapter','authtable'), $columns)
				->whereEqual(Config::getValue('DBAuthAdapter','uidfield'), $cred['username']);
			if(($result = $query->execute()) !== false)
			{
				$row = $result->fetch(\PDO::FETCH_ASSOC);
				//Secondary check to make sure the results did not differ from MySQL's response.
				if(strtolower($row[Config::getValue('DBAuthAdapter','uidfield')]) == strtolower($cred['username'])
					&& password_verify($pass, $row[Config::getValue('DBAuthAdapter','pwfield')]))
				{
					$this->uid = $cred['username'];
					return true;
				}
			}
		}
		return false;	
	}

	/**
	 * Gets the access level for the supplied $uid.
	 * @return int
	 */
	public function getLevel()
	{
		$query = Query::select(Config::getValue('DBAuthAdapter','authtable'), [Config::getValue('DBAuthAdapter','rolefield')])
			->whereEqual(Config::getValue('DBAuthAdapter','uidfield'),$this->uid);
		if(($result = $query->execute()) !== false)
		{
			$level = (int)$result->fetchColumn(0);
			if($level < 0)
			{
				return 0;
			}
			else
			{
				return $level;
			}
		}
		else
		{
			return 0;
		}
	}
	
	/**
	 * Returns the User ID from the adapter.
	 * @return string
	 */
	public function getUserId()
	{
		return $this->uid;
	}
}