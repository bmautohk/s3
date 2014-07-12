<?php
class Model_Factory_GiftForm {
	public $factory;
	public $action;
	public $gift_id;
	public $gift;
	
	public function __construct($factory) {
		$this->factory = $factory;
	}
	
	public function populate($post) {
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->gift_id = isset($post['gift_id']) ? $post['gift_id'] : NULL;
		
		if (!empty($this->gift_id)) {
			// Existing record
			$this->gift = new Model_Gift($this->gift_id);
		} else {
			// New record
			$this->gift = new Model_Gift();
		}
		
		$this->gift->values($post);
	}
	
	public function init() {
		
	}
	
	public function retrieve($gift_id) {
		$this->gift = new Model_Gift($gift_id);
		if ($this->gift->loaded()) {
			return true;
		} else {
			return false;
		}
	}
	
	public function saveAction() {
		return $this->save();
	}

	private function save() {
		$db = Database::instance();
		$db->begin();
		
		try {
			$username = Auth::instance()->get_user()->username;
			if (!$this->gift->loaded()) {
				// New record
				$this->gift->factory = Model_Gift::getFactoryCode($this->factory);
				$this->gift->status = Model_Gift::STATUS_INIT;
				$this->gift->created_by = $username;
				$this->gift->create_date = DB::expr('current_timestamp');
			}
			
			$this->gift->last_updated_by = $username;
			
			// Save to DB
			$this->gift->save();
			
			// Upload image
			if (isset($_FILES['picture1']) && Upload::not_empty($_FILES['picture1'])) {
				$fileName = $this->saveImage($_FILES['picture1'], $this->gift->id, '1');
				if ($fileName) {
					$this->gift->picture1 = $fileName;
				} else {
					$this->warnings[] = 'Fail to upload picture 1';
				}
			}
			
			if (isset($_FILES['picture2']) && Upload::not_empty($_FILES['picture2'])) {
				$fileName = $this->saveImage($_FILES['picture2'], $this->gift->id, '2');
				if ($fileName) {
					$this->gift->picture2 = $fileName;
				} else {
					$this->warnings[] = 'Fail to upload picture 2';
				}
			}
			
			if (isset($_FILES['picture3']) && Upload::not_empty($_FILES['picture3'])) {
				$fileName = $this->saveImage($_FILES['picture3'], $this->gift->id, '3');
				if ($fileName) {
					$this->gift->picture3 = $fileName;
				} else {
					$this->warnings[] = 'Fail to upload picture 3';
				}
			}
			
			// Update picture path
			$this->gift->save();
			
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		return true;
	}
	
	private function saveImage($image, $giftId, $fileNamePrefix) {
		if (
				! Upload::valid($image) OR
				! Upload::type($image, array('jpg', 'jpeg', 'png', 'gif'))) {
				
			$this->warnings[] = 'Only support picture type jpg/png/gif.';
			return false;
		}

		$directory = GIFT_IMAGE_UPLOAD_DIRECTORY.$giftId.'/';

		try {
	
			// Create directory
			if (!file_exists($directory)) {
				mkdir($directory, 0777, true);
			}
				
			$ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
			$fileName = $fileNamePrefix.'.'.$ext;
	
			if ($file = Upload::save($image, $fileName, $directory)) {
				Image::factory($file)->resize(200, 200, Image::AUTO)->save($directory.'s_'.$fileName);
				return $fileName;
			}
		} catch (Exception $e) {
			$this->warnings[] = $e->getMessage();
			return false;
		}
	
		return false;
	}
}