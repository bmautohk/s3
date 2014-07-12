<?php defined('SYSPATH') or die('No direct script access.');

class Model_PageForm {
	
	public $item_count;
	
	public $current_page;
	
	public $page_size = PAGE_SIZE;
	
	public $page_url;
	
	public function populate($post) {
		$this->item_count = isset($post['item_count']) ? $post['item_count'] : NULL;
		$this->current_page = isset($post['current_page']) ? $post['current_page'] : 1;
	}
	
	public function getData($limit, $offset) {
		// Implemented by subclass
	}
	
	public function getCriteria() {
		// Implemented by subclass
	}
	
	public function getQueryString() {
		// Implemented by subclass
	}
	
	public function search() {
		if ($this->item_count == NULL) {
			// Get count
			$this->item_count = $this->getCount();
		}
		
		if ($this->current_page == NULL || $this->current_page <= 0) {
			$this->current_page = 1;
		}
		
		$offset = $this->page_size * ($this->current_page - 1);
		
		return $this->getData($this->page_size, $offset);
	}

	public function getCount() {
		$criteria = $this->getCriteria();
		return $criteria->count_all();
	}
	
	public function pager() {
		return $this->generate_pager($this->item_count, $this->current_page, $this->page_size, URL::base().$this->page_url, $this->getQueryString());
	}
	
	private function generate_pager($num_rows, $zpage, $per_page, $page_url, $queryString) {
		$html = '';
		
		$showeachside = 9;
		$eitherside = ($showeachside + 1) / 2;
	
		if (!$zpage) {
			$zpage = 1;
		}
	
		$prev_page = $zpage - 1;
		$next_page = $zpage + 1;
	
		// Set up specified page
		$page_start = ($per_page * $zpage) - $per_page;
		//$num_rows = mysql_num_rows($query);
		if ($num_rows <= $per_page) {
			$num_pages = 1;
		} else if (($num_rows % $per_page) == 0) {
			$num_pages = ($num_rows / $per_page);
		} else {
			$num_pages = ($num_rows / $per_page) + 1;
		}
		$num_pages = (int) $num_pages;
	
		$html .= "<div class=\"pager\">Page $zpage of $num_pages<br>( $num_rows records )<br>";
	
		// First Page
		$html .= "<a href=\"$page_url?item_count=$num_rows&current_page=1".$queryString."\">First</a> ";
	
		//
		// Now the pages are set right, we can
		// perform the actual displaying...
		if ($prev_page) {
			$html .= "<a href=\"$page_url?item_count=$num_rows&current_page=$prev_page".$queryString."\">Prev</a>";
		}
		// Page # direct links
		// If you don't want direct links to eac
		//     h page, you should be able to
		// safely remove this chunk.
	
		if($zpage > $eitherside) {
			$start = $zpage - $eitherside + 1;
			if ($start > $num_pages - $showeachside) $start = max($num_pages - $showeachside + 1, 1);
		}
		else {
			$start = 1;
		}
	
		if($zpage+$eitherside <= $num_pages) {
			$end = $zpage + $eitherside - 1;
			if ($end < $showeachside) $end = min($showeachside, $num_pages);
		}
		else {
			$end = $num_pages;
		}
	
	
		if ($start != 1) {
			$html .= " .... ";
		}
		for ($i = $start; $i <= $end; $i++) {
			if ($i != $zpage) {
				$html .= " <a href=\"$page_url?item_count=$num_rows&current_page=$i".$queryString."\">$i</a> ";
			} else {
				$html .= " $i ";
			}
		}
		
		if($end != $num_pages) {
			$html .= " .... ";
		}
		
		// Next
		if ($zpage != $num_pages) {
			$html .= "<a href=\"$page_url?item_count=$num_rows&current_page=$next_page".$queryString."\">Next</a> ";
		}
		
		// Last Page
		$html .= "<a href=\"$page_url?item_count=$num_rows&current_page=$num_pages".$queryString."\">Last</a> ";
		
		$html .= "</div>\n";
		
		return $html;
	}
}