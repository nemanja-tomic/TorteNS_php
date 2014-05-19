<?php
/**
 * Created by PhpStorm.
 * User: Nemanja
 * Date: 5/18/14
 * Time: 9:56 AM
 */

class ProductController extends BaseController {
	public function __construct(ConfigManager $config) {
		parent::__construct($config);
			$this->db = new ProductDb($config);
	}

	public function deleteProduct($id) {
		try {
			$this->db->deleteRecord($id);
		} catch (Exception $ex) {
			$this->HandleException($ex);
		}
	}

	public function filterProducts(ProductFilter $filter) {
		$productList = array();
		try {
			$productList = $this->db->fetchProducts($filter);

			/** @var $product Product */
			foreach ($productList as $product) {
				$product->images = $this->db->fetchProductImages($product->idProizvoda);
			}
		} catch (Exception $ex) {
			$this->HandleException($ex);
		}
		return $productList;
	}

	public function getProduct($id) {
		$product = new Product();
		try {
			$product = $this->db->getRecord($id);
			$product->images = $this->db->fetchProductImages($product->idProizvoda);
		} catch (Exception $ex) {
			$this->HandleException($ex);
		}
		return $product;
	}

	public function insertProduct($post) {
		$productId = 0;
		try {
			$productId = $this->db->insertRecord($this->prepareProductData($post));
		} catch (Exception $ex) {
			$this->HandleException($ex);
		}
		return $productId;
	}

	/**
	 * @param $post array Data from $_POST
	 * @return Product
	 */
	private function prepareProductData($post) {
		$product = new Product();
		$product->naziv = $post['naziv'];
		if (isset($post['kolicina']))
			$product->kolicina = $post['kolicina'];
		if (isset($post['tezina']))
			$product->tezina = $post['tezina'];
		$product->cena = $post['cena'];
		$product->opis = $post['opis'];
		$product->idGrupe = $post['tip'];
		return $product;
	}

	public function bindProductImage($productId, $imageId) {
		try {
			$this->db->bindProductImage($productId, $imageId);
		} catch (Exception $ex) {
			$this->HandleException($ex);
		}
	}
} 