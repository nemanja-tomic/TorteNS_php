<?php
$response->message = "Invalid request!";
if (isset($_POST["action"]) && $_POST["action"] != "") {
	try {
		$data = (isset($_POST["data"])) ? $_POST["data"] : "";
		$api = new Api($_POST["action"], $data);
		$response = $api->Execute();
	} catch (Exception $ex) {
		$response->message = $ex->getMessage();
		$response->success = false;
	}
}
echo json_encode($response);
exit();

class Api {

	private $action;
	private $data;
	/**
	 * @var ConfigManager
	 */
	private $configManager;
	/**
	 * @var ClientController
	 */
	private $clientController;
	/**
	 * @var ProductController
	 */
	private $productController;
	/**
	 * @var GroupController
	 */
	private $groupController;
	/**
	 * @var ImageController
	 */
	private $imageController;
	/**
	 * @var OrderController
	 */
	private $orderController;

	public function __construct($action, $data) {
		require_once 'autoloader.php';
		Autoloader::init();

		$this->action = strtolower($action);
		$this->data = json_decode($data);

		$this->Initialize();
	}

	private function Initialize() {
		$this->configManager = new ConfigManager();

        //quick 'fix' - create pdo object, and pass it in controllers constructors
        //TODO: refactor to more decent dependency injection
        $server = $this->configManager->getDatabaseHost();
        $username = $this->configManager->getDatabaseUsername();
        $password = $this->configManager->getDatabasePassword();
        $dbName = $this->configManager->getDatabaseName();
        $db = new PDO("mysql:host={$server};dbname={$dbName}", $username, $password);

		$this->clientController = new ClientController($this->configManager, $db);
		$this->productController = new ProductController($this->configManager, $db);
        $this->groupController = new GroupController($this->configManager, $db);
		$this->imageController = new ImageController($this->configManager, $db);
		$this->orderController = new OrderController($this->configManager, $db);

		if ($this->configManager->isDebugMode()) {
			ini_set('display_startup_errors', 1);
			ini_set('display_errors', 1);
			error_reporting(-1);
		}
        
	}

	/**
	 * Executes the API method as provided in request "action" parameter.
	 *
	 * @return Response Response object containing execution results in Message, Success and Data properties.
	 * @throws BadMethodCallException Thrown if invalid or unknown method is called.
	 */
	public function Execute() {
		$response = new Response();
		switch ($this->action) {
			case ApiActions::GetClients:
				$response->data = $this->clientController->filterClients($this->data->imeF, $this->data->prezimeF, $this->data->emailF, $this->data->telefonF);
				break;
			case ApiActions::GetClient:
				$response->data = $this->clientController->getClient($this->data->id);
				break;
			case ApiActions::DeleteClient:
				$this->clientController->deleteClient($this->data->id);
				$response->message = "Client successfully deleted!";
				break;
			case ApiActions::InsertClient:
				$this->clientController->insertClient($this->data);
				$response->message = "Client successfully added!";
				break;
			case ApiActions::UpdateClient:
				$this->clientController->updateClient($this->data);
				$response->message = "Client successfully updated!";
				break;
			case ApiActions::DeleteProduct:
				$product = $this->productController->getProduct($this->data->id);
				/** @var $image Image */
				foreach ($product->images as $image) {
					try {$this->imageController->deleteImage($image->idSlike);} catch (Exception $ex) {}
				}
				$this->productController->deleteProduct($product->idProizvoda);
				break;
			case ApiActions::FilterProducts:
				$filter = new ProductFilter($this->data->idGrupe, $this->data->cena, $this->data->naziv, $this->data->opis);
				$response->data = $this->productController->filterProducts($filter);
				break;
			case ApiActions::GetProduct:
				$response->data = $this->productController->getProduct($this->data->id);
				break;
			case ApiActions::GetProducts:
				$response->data = $this->productController->filterProducts(new ProductFilter($this->data->groupId));
				break;
			case ApiActions::InsertProduct:
				$group = $this->groupController->getGroupName($_POST["tip"]);
				$productId = $this->productController->insertProduct($_POST);
				if ($_FILES['slika']['name'] <> "") {
					$imageId = $this->imageController->insertImage($_FILES['slika'], $group->naziv);
					$this->productController->bindProductImage($productId, $imageId);
				}
				$response->message = "Product successfully added!";
				break;
			case ApiActions::UpdateProduct:
				$this->productController->updateProduct((array)$this->data);
				$response->message = "Product successfully updated!";
				break;
			case ApiActions::InsertProductImage:
				$group = $this->groupController->getGroupName($_POST["tipHidden"]);
				$imageId = $this->imageController->insertImage($_FILES['imgFile'], $group->naziv);
				$this->productController->bindProductImage($_POST['idProizvodaHidden'], $imageId);
				break;
			case ApiActions::DeleteImage:
				$this->imageController->deleteImage($this->data->id);
				$response->message = "Image successfully deleted!";
				break;
			case ApiActions::GetImages:
				$product = $this->productController->getProduct($this->data->id);
				$response->data = $product->images;
				$response->hasData = count($response->data) > 0;
				break;
			case ApiActions::FilterOrders:
				$response->data = $this->orderController->filterOrders(new OrderFilter($this->data->ime, $this->data->naziv, $this->data->cena, $this->data->napomena));
				break;
			case ApiActions::FilterOldOrders:
				$response->data = $this->orderController->filterOrders(new OrderFilter($this->data->ime, $this->data->naziv, $this->data->cena, $this->data->napomena), false);
				break;
			case ApiActions::GetOrder:
				$order = $this->orderController->getOrder($this->data->id);
				$order->images = $this->productController->getProduct($order->idProizvoda)->images;
				$response->data = $order;
				break;
            case ApiActions::GetIncomingOrders:
                $response->data = $this->orderController->getIncomingOrders();
                break;
			case ApiActions::GetOrdersByDate:
				$response->data = $this->orderController->getOrdersByDate($this->data->datum);
				$response->hasData = count($response->data) > 0;
				break;
			case ApiActions::GetOrdersByClient:
				$response->data = $this->orderController->getOrdersByClient($this->data->id, $this->data->newOrders);
				$response->hasData = count($response->data) > 0;
				break;
			case ApiActions::GetAllOrderDates:
				$response->data = $this->orderController->getAllOrderDates();
				break;
			case ApiActions::UpdateOrder:
				$this->orderController->updateOrder($this->data);
				$response->message = "Order successfully updated!";
				break;
			case ApiActions::GetOrderImages:
				$order = $this->orderController->getOrder($this->data->id);
				$response->data = $order->images;
				break;
			case ApiActions::DeleteOrder:
				$order = $this->orderController->getOrder($this->data->id);
				/** @var $image Image */
				foreach ($order->images as $image) {
					try {$this->imageController->deleteImage($image->idSlike);} catch(Exception $ex) {}
				}
				$this->orderController->deleteOrder($this->data->id);
				$response->message = "Order successfully deleted!";
				break;
			case ApiActions::InsertOrder:
				$this->orderController->insertOrder($this->data);
				$response->message = "Order successfully created!";
				break;
			default:
				throw new BadMethodCallException("Invalid API method called: ".$this->action);
		}
		return $response;
	}
}







