<?php
namespace VampiRUS\MxlReader;

class Mxl {

	const MAGIC = 'MOXCEL';

	private $fp = null;
	private $fileName = '';
	private $header = [];

	private $cells = [];

	private $cols = [];

	private $props = [];

	public function __construct($fileName) {
		$this->fileName = $fileName;
		$this->open();
	}

	protected function open() {
		if (!file_exists($this->fileName)) {
            throw new \Exception(sprintf('File %s cannot be found', $this->fileName));
        }
		$this->fp = fopen($this->fileName, "rb");
		$this->readHeader();
		$this->readFonts();
		$this->readHFProp();
		return $this->fp != false;
	}

	protected function readHeader() {
		$raw_header = fread($this->fp, 25);
		$this->header = unpack("a6magic/h10unused/cversion/h2unused2/lcolumns/lrows/lembeded", $raw_header);

		if ($this->header['magic'] != self::MAGIC) {
			throw new \Exception(sprintf('File %s is not MXL', $this->fileName));
		}

		if ($this->header['version'] != 6) {
			throw new \Exception('Unsupported MXL version');
		}
		$raw_table = $this->readBytesAsArray(30);
	}

	protected function readFonts() {
		$counter = $this->readShort();
		if($counter) {
			//TODO
		}
		$raw_counter = fread($this->fp, 2);
		$this->readLong();//(?)
	}

	protected function readHFProp() {
		$this->props['top'] = $this->readBytesAsArray(30);
		$this->props['bottom'] = $this->readBytesAsArray(30);
	}

	public function getColumnCount() {
		return $this->header['columns'];
	}

	public function getRowCount() {
		return $this->header['rows'];
	}

	public function getColumns () {
		if ($this->cols) {
			return $this->cols;
		}
		$cols = [];
		$raw_column_count = fread($this->fp, 2);
		for($i = 0; $i < $this->header['columns']; $i++){
			$cols[] = $this->readLong();
		}
		fread($this->fp, 2);
		for($i = 0 ; $i < $this->header['columns']; $i++) {
			$this->cols[$cols[$i]] = $this->readBytesAsArray(30);//columns settings
		}

		return $this->cols;
	}

	public function getHFProps() {
		return $this->props;
	}

	public function getDataAsArray() {
		$this->getColumns();
		if ($this->cells) {
			return $this->cells;
		}
		fread($this->fp, 2); //total rows
		for($i = 0; $i < $this->header['rows']; $i++){
			fread($this->fp, 4);//row number
		}
		fread($this->fp, 2); //total rows
		for($i = 0; $i < $this->header['rows']; $i++){
			$this->cells[$i][0] = $this->cell();
			//cells
			$cells_number = $this->readShort();
			for($j = 0; $j < $cells_number; $j++) {
				fread($this->fp, 4);
			}
			fread($this->fp, 2);
			for($j = 0; $j < $cells_number; $j++) {
				$this->cells[$i][$j+1] = $this->cell();
			}
		}

		return $this->cells;
	}

	private function cell() {
		$data = '';
		$cell_info = $this->readBytesAsArray(30);
		if($cell_info[4] == 0x40 || $cell_info[4] == 0x80 || $cell_info[4] == 0xC0) {
			$length = $this->readByte();
			$data = $this->readString($length);
		}

		if ($cell_info[4] == 0xC0) {
			$length = $this->readByte();
			$options = $this->readString($length);
		}
		return $data;
	}

	private function readByte() {
		$data = fread($this->fp, 1);
		$byte = unpack("C", $data);
		return $byte[1];
	}

	private function readBytesAsArray($count) {
		$data = fread($this->fp, $count);
		$array = unpack("C*", $data);
		return $array;
	}

	private function readShort() {
		$data = fread($this->fp, 2);
		$short = unpack("S", $data);
		return $short[1];
	}

	private function readLong() {
		$data = fread($this->fp, 4);
		$long = unpack("L", $data);
		return $long[1];
	}

	private function readString($length) {
		$data = fread($this->fp, $length);
		$string = unpack("a*", $data);
		return iconv("cp1251","utf8", $string[1]);
	}


}