<?php
/**
* CSVParser
* Parse csv file data to array, object, json.
*
* @package : CSVParser
* @category : Library
* @author : Unic Framework
* @link : https://github.com/unicframework/csv-parser
*/

namespace CSVParser;
use Exception;

class CSV {
  private $delimiter = ',';
  private $enclosure = '"';
  private $ignoreHeader = false;
  private $ignoreHeaderCase = true;
  private $ignoreEnclosure = false;
  private $headerOffset = 0;
  private $header = [];
  private $limit = [];
  private $parsedData = [];

  /**
  * Parse data
  *
  * @param string $data
  * @return void
  */
  public function parse($data) {
    $rawData = NULL;
    $dataType = NULL;
    //Check data type
    if(is_array($data)) {
      $rawData = $data;
      $dataType = 'array';
    } else if(is_object($data)) {
      $rawData = (array) $data;
      $dataType = 'array';
    } else if($this->is_json($data)) {
      $rawData = json_decode($data, true);
      $dataType = 'array';
    } else if(is_string($data)) {
      if(is_file($data)) {
        if(is_readable($data)) {
          //Read data from file
          $fileData = file($data, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
          $tmpData = [];
          foreach($fileData as $row) {
            $tmpData[] = array_map(function($value) {
              $data = trim($value);
              //Remove enclosure
              if($this->ignoreEnclosure === true) {
                return $data;
              } else {
                return is_string($data) ? trim($data, $this->enclosure) : $data;
              }
            }, str_getcsv($row, $this->delimiter, $this->enclosure));
          }
          $rawData = $tmpData;
          $dataType = 'csv';
        } else {
          //Throw error
          throw new Exception('Error: Can not read file, permission denied');
        }
      } else {
        //Throw error
        throw new Exception('Error: Csv file not found');
      }
    } else {
      //Throw error
      throw new Exception('Error: CSVParser invalid data type');
    }

    //Parse header
    if($dataType == 'array') {
      if($this->ignoreHeader == false) {
        //Parse user custom header
        if(empty($this->header) && !empty($rawData)) {
          if(isset($rawData[$this->headerOffset])) {
            $this->header = array_keys((array)$rawData[$this->headerOffset]);
          } else {
            throw new Exception('Error : header not found at offset '.$this->headerOffset);
          }
        }
      }
    } else {
      if($this->ignoreHeader == false) {
        //Parse user custom header
        if(!empty($this->header)) {
          //Ignore header
          if(isset($rawData[$this->headerOffset])) {
            unset($rawData[$this->headerOffset]);
          }
        } else {
          if(isset($rawData[$this->headerOffset])) {
            $this->header = $rawData[$this->headerOffset];
            unset($rawData[$this->headerOffset]);
          } else {
            throw new Exception('Error : header not found at offset '.$this->headerOffset);
          }
        }
      }
    }

    //Parse data
    if(!empty($rawData)) {
      //Parse body data
      $parseData = [];
      foreach($rawData as $row) {
        $tmpData = array();
        $i = 0;
        foreach($row as $key => $value) {
          if(isset($this->header[$i]) && $this->header[$i] != '') {
            $tmpData[$this->header[$i]] = $value;
          } else {
            $tmpData[count($tmpData)] = $value;
          }
          $i++;
        }
        if(!empty($tmpData)) {
          $parseData[] = $tmpData;
        }
      }
      $this->parsedData = $parseData;
    }
  }

  /**
  * Set csv delimiter
  *
  * @param string $delimiter
  * @return void
  */
  public function setDelimiter(string $delimiter) {
    $this->delimiter = $delimiter;
  }

  /**
  * Set csv enclosure
  *
  * @param string $enclosure
  * @return void
  */
  public function setEnclosure(string $enclosure) {
    $this->enclosure = $enclosure;
  }

  /**
  * Ignore csv header
  *
  * @param boolean $ignore
  * @return void
  */
  public function ignoreHeader(bool $ignore) {
    $this->ignoreHeader = $ignore;
  }

  /**
  * Ignore csv header case
  *
  * @param boolean $ignore
  * @return void
  */
  public function ignoreHeaderCase(bool $ignore) {
    $this->ignoreHeaderCase = $ignore;
  }

  /**
  * Ignore csv enclosure
  *
  * @param string $ignore
  * @return void
  */
  public function ignoreEnclosure(string $ignore) {
    $this->ignoreEnclosure = $ignore;
  }

  /**
  * Set csv header offset
  *
  * @param integer $offset
  * @return void
  */
  public function headerOffset(int $offset) {
    $this->headerOffset = $offset;
  }

  /**
  * Set csv header
  *
  * @param array $header
  * @return void
  */
  public function setHeader(array $header) {
    $this->header = $header;
  }

  /**
  * Get csv header
  *
  * @return array
  */
  public function getHeader() {
    return $this->header;
  }

  /**
  * Get parsed data row count
  *
  * @return integer
  */
  public function rowCount() : int {
    return count($this->parsedData);
  }

  /**
  * Get parsed data header count
  *
  * @return integer
  */
  public function headerCount() : int {
    return count($this->header);
  }

  /**
  * Get sum of given field
  *
  * @param $field
  * @return integer|float
  */
  public function sum(string $field) {
    return array_sum(array_column($this->toArray([$field]), $field));
  }

  /**
  * Get min of given field
  *
  * @param $field
  * @return mixed
  */
  public function min(string $field) {
    return min(array_column($this->toArray([$field]), $field));
  }

  /**
  * Get max of given field
  *
  * @param $field
  * @return mixed
  */
  public function max(string $field) {
    return max(array_column($this->toArray([$field]), $field));
  }

  /**
  * Get average of given field
  *
  * @param $field
  * @return integer|float
  */
  public function average(string $field) {
    $data= array_column($this->toArray([$field]), $field);
    $count = count($data);
    if($count > 0) {
      return array_sum($data)/$count;
    } else {
      return array_sum($data);
    }
  }

  /**
  * Set data limit
  *
  * @param array $limit
  * @return void
  */
  public function limit(...$limit) {
    if(count($limit) == 2) {
      if(isset($limit[0])) {
        $this->limit['start'] = (int) $limit[0];
      }
      if(isset($limit[1])) {
        $this->limit['end'] = (int) $limit[1];
      }
    } else if(count($limit) == 1) {
      $this->limit['start'] = 0;
      $this->limit['end'] = (int) $limit[0];
    }
    if(isset($this->limit['start']) && isset($this->limit['end'])) {
      if($this->limit['end'] < $this->limit['start']) {
        $this->limit = [];
      } else {
        $this->limit['start'] = $this->limit['start'] != 0 ? $this->limit['start'] - 1 : $this->limit['start'];
        $this->limit['end'] = $this->limit['end'] != 0 ? $this->limit['end'] - $this->limit['start'] : $this->limit['end'];
      }
    }
  }

  /**
  * Get parsed data to array
  *
  * @param array $header
  * @return array
  */
  public function toArray(array $header=NULL) : array {
    $parsedHeader = [];
    $parsedData = [];
    //Set data limit
    if(!empty($this->limit)) {
      if(isset($this->limit['start']) && isset($this->limit['end'])) {
        $tmpParsedData = array_slice($this->parsedData, $this->limit['start'], $this->limit['end']);
      } else if(isset($this->limit['start'])) {
        $tmpParsedData = array_slice($this->parsedData, $this->limit['start']);
      }
    } else {
      $tmpParsedData = $this->parsedData;
    }

    //Check header is valid or not
    if(!empty($header)) {
      if(!empty($this->header) && $this->ignoreHeaderCase == true) {
        $ignoreHeaderCase = array_map('strtolower', $this->header);
        $tmpHeader = array_combine($ignoreHeaderCase, $this->header);
      } else {
        $tmpHeader = array_combine($this->header, $this->header);
      }
      //Parse header data
      if(!empty($tmpHeader)) {
        foreach($header as $col) {
          $tmpCol = $col;
          if($this->ignoreHeaderCase == true) {
            $col = strtolower($col);
          }
          //Check header exists or not
          if(!array_key_exists($col, $tmpHeader)) {
            //Throw error header not found
            throw new Exception("Error: '".$tmpCol."' header not found");
          } else {
            $parsedHeader[$tmpCol] = $tmpHeader[$col];
          }
        }
      } else {
        //Check header index exists or not
        foreach($header as $col) {
          if(is_int($col) && isset($tmpParsedData[$this->headerOffset])) {
            $col = $col == 0 ? $col : $col - 1;
            if(!array_key_exists($col, $tmpParsedData[$this->headerOffset])) {
              //Throw error header not found
              throw new Exception("Error: '".$col."' header not found");
            } else {
              $parsedHeader[$col] = $col;
            }
          } else {
            //Throw error header not found
            throw new Exception("Error: '".$col."' header not found");
          }
        }
      }

      //Parse data
      foreach($tmpParsedData as $row) {
        $tmpRow = [];
        foreach($parsedHeader as $key => $val) {
          $tmpRow[$key] = $row[$val];
        }
        if(!empty($tmpRow)) {
          $parsedData[] = $tmpRow;
        }
      }
    } else {
      $parsedData = $tmpParsedData;
    }
    $this->limit = [];
    return $parsedData;
  }

  /**
  * Get parsed data to object
  *
  * @param array $header
  * @return object
  */
  public function toObject(array $header=NULL) : object {
    return (object) $this->toArray($header);
  }

  /**
  * Get parsed data to json
  *
  * @param array $header
  * @return string
  */
  public function toJson(array $header=NULL) : string {
    return json_encode($this->toArray($header));
  }

  /**
  * Get parsed data to csv
  *
  * @param array $header
  * @return string
  */
  public function toCsv(array $header=NULL) : string {
    $parsedData = $this->toArray($header);
    $csvData = '';
    if(!empty($this->header)) {
      if(!empty($header)) {
        //Add csv enclosure
        if($this->ignoreEnclosure === false) {
          $csvData .= $this->enclosure.implode($this->enclosure.$this->delimiter.$this->enclosure, $header).$this->enclosure.PHP_EOL;
        } else {
          $csvData .= implode($this->delimiter, $header).PHP_EOL;
        }
      } else {
        //Add csv enclosure
        if($this->ignoreEnclosure === false) {
          $csvData .= $this->enclosure.implode($this->enclosure.$this->delimiter.$this->enclosure, $this->header).$this->enclosure.PHP_EOL;
        } else {
          $csvData .= implode($this->delimiter, $this->header).PHP_EOL;
        }
      }
    }
    if(!empty($parsedData)) {
      foreach($parsedData as $row) {
        //Add csv enclosure
        if($this->ignoreEnclosure === false) {
          $csvData .= $this->enclosure.implode($this->enclosure.$this->delimiter.$this->enclosure, $row).$this->enclosure.PHP_EOL;
        } else {
          $csvData .= implode($this->delimiter, $row).PHP_EOL;
        }
      }
    }
    return $csvData;
  }

  /**
  * Check Json format is valid or not.
  *
  * @param mixed $data
  * @return boolean
  */
  private function is_json($data) : bool {
    return is_array($data) ? false : is_array(json_decode($data, true));
  }
}
?>
