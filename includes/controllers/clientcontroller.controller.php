<?php
/**
 * Created by PhpStorm.
 * User: Nemanja
 * Date: 5/16/14
 * Time: 11:51 PM
 */

class ClientController extends BaseController {

	public function __construct(ConfigManager $config, PDO $db) {
		parent::__construct($config);
		$this->db = new ClientDb($config, $db);
	}

	/**
	 * @param $clientId
	 * @return Client
	 */
	public function getClient($clientId) {
		$clientData = new Client();
		try {
			$clientData = $this->db->getRecord($clientId);
			$clientData->statusText = ClientStatus::GetConstantName($clientData->status);
		} catch (Exception $ex) {
			$this->HandleException($ex);
		}
		return $clientData;
	}

	/**
	 * @param $fname
	 * @param $lname
	 * @param $email
	 * @param $phone
	 * @return array
	 */
	public function filterClients($fname, $lname, $email, $phone) {
		$clientList = array();
		try {
			$clientList = $this->db->fetchClients($fname, $lname, $email, $phone);

			/** @var $client Client */
			foreach ($clientList as $client) {
				$client->statusText = ClientStatus::GetConstantName($client->status);
			}
		} catch (Exception $ex) {
			$this->HandleException($ex);
		}
		return $clientList;
	}

	/**
	 * @param $clientId
	 */
	public function deleteClient($clientId) {
		try {
			$this->db->deleteRecord($clientId);
		} catch (Exception $ex) {
			$this->HandleException($ex);
		}
	}

	public function insertClient($data) {
		try {
			$this->db->insertRecord($this->prepareClientData($data));
		} catch (Exception $ex) {
			$this->HandleException($ex);
		}
	}

	public function updateClient($data) {
		try {
			$this->db->updateRecord($this->prepareClientData($data));
		} catch (Exception $ex) {
			$this->HandleException($ex);
		}
	}

	/**
	 * @param $data
	 * @return Client
	 */
	private function prepareClientData($data) {
		$client = new Client();
		foreach ($data as $key => $value) {
			$client->$key = trim($value);
		}
		return $client;
	}
}