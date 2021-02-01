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
  private $ignoreHeader = false;
  private $ignoreHeaderCase = true;
  private $headerOffset = 0;
  private $header = [];
  private $rowCount = 0;
  private $limit = [];
  private $parsedData = [];

  /**
  * Parse data
  *
  * @param string $data
  * @return void
  */
  function parse($data) {
    $rawData = NULL;
    //Check data type
    if(is_array($data)) {
      $rawData = $data;
    } else if(is_object($data)) {
      $rawData = (array) $data;
    } else if($this->is_json($data)) {
      $rawData = json_decode($data, true);
    } else if(is_string($data)) {
      if(is_file($data)) {
        if(is_readable($data)) {
          //Read data from file
          $fileData = file($data, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
          $tmpData = [];
          foreach($fileData as $row) {
            $tmpData[] = array_map('trim', explode($this->delimiter, $row));
          }
          $rawData = $tmpData;
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
    //Parse data
    if(!empty($rawData)) {
      if($this->ignoreHeader == true) {
        if(!empty($this->header)) {
          //Parse body data
          $parseData = [];
          foreach($rawData as $row) {
            $tmpData = array();
            foreach($row as $key => $value) {
              if(isset($this->header[$key])) {
                $tmpData[$this->header[$key]] = $value;
              } else {
                $tmpData[count($tmpData)] = $value;
              }
            }
            if(!empty($tmpData)) {
              $parseData[] = $tmpData;
            }
          }
          $this->parsedData = $parseData;
        } else {
          $this->parsedData = $rawData;
        }
      } else {
        //Parse header
        if(empty($this->header)) {
          if($this->headerOffset == 0) {
            $this->header = $rawData[0];
            //Remove header from data
            array_shift($rawData);
          } else if(isset($rawData[$this->headerOffset])) {
            $this->header = $rawData[$this->headerOffset];
            //Remove header from data
            unset($rawData[$this->headerOffset]);
          } else {
            //Throw error header not found
            throw new Exception('Error: Header not found');
          }
        } else {
          if($this->headerOffset == 0) {
            //Remove header from data
            array_shift($rawData);
          } else if(isset($rawData[$this->headerOffset])) {
            //Remove header from data
            unset($rawData[$this->headerOffset]);
          } else {
            //Throw error header not found
            throw new Exception('Error: Header not found');
          }
        }
        //Parse body data
        $parseData = [];
        foreach($rawData as $row) {
          $tmpData = array();
          foreach($row as $key => $value) {
            if(isset($this->header[$key])) {
              $tmpData[$this->header[$key]] = $value;
            } else {
              $tmpData[count($tmpData)] = $value;
            }
          }
          if(!empty($tmpData)) {
            $parseData[] = $tmpData;
          }
        }
        $this->parsedData = $parseData;
      }
    }
  }

  /**
  * Set csv delimiter
  *
  * @param string $delimiter
  * @return void
  */
  function setDelimiter(string $delimiter) {
    $this->delimiter = $delimiter;
  }

  /**
  * Ignore csv header
  *
  * @param boolean $ignore
  * @return void
  */
  function ignoreHeader(bool $ignore) {
    $this->ignoreHeader = $ignore;
  }

  /**
  * Ignore csv header case
  *
  * @param boolean $ignore
  * @return void
  */
  function ignoreHeaderCase(bool $ignore) {
    $this->ignoreHeaderCase = $ignore;
  }

  /**
  * Set csv header offset
  *
  * @param integer $offset
  * @return void
  */
  function headerOffset(int $offset) {
    $this->headerOffset = $offset;
  }

  /**
  * Set csv header
  *
  * @param array $header
  * @return void
  */
  function setHeader(array $header) {
    $this->header = $header;
  }

  /**
  * Get parsed data row count
  *
  * @return integer
  */
  function rowCount() : int {
    return count($this->parsedData);
  }

  /**
  * Set data limit
  *
  * @param array $limit
  * @return void
  */
  function limit(...$limit) {
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
  function toArray(array $header=NULL) : array {
    $parseHeader = [];
    $parseData = [];
    if(!empty($header)) {
      //Check header is valid or not
      if(!empty($this->header)) {
        if($this->ignoreHeaderCase == true) {
          $ignoreHeaderCase = array_map('strtolower', $this->header);
          $tmpHeader = array_combine($ignoreHeaderCase, $this->header);
        } else {
          $tmpHeader = array_combine($this->header, $this->header);
        }
      } else if(!empty($this->parsedData)) {
        $tmpHeader = array_combine(array_keys($this->parsedData[0]), array_keys($this->parsedData[0]));
      }
      //Parse header data
      foreach($header as $col) {
        $tmpCol = $col;
        if($this->ignoreHeaderCase == true) {
          $col = strtolower($col);
        }
        if(!array_key_exists($col, $tmpHeader)) {
          //Throw error header not found
          throw new Exception("Error: '".$tmpCol."' header not found");
        } else {
          $parseHeader[$tmpCol] = $tmpHeader[$col];
        }
      }
      //Parse body data
      if(!empty($this->limit)) {
        if(isset($this->limit['start']) && isset($this->limit['end'])) {
          $tmpParsedData = array_slice($this->parsedData, $this->limit['start'], $this->limit['end']);
        } else if(isset($this->limit['start'])) {
          $tmpParsedData = array_slice($this->parsedData, $this->limit['start']);
        }
      } else {
        $tmpParsedData = $this->parsedData;
      }
      foreach($tmpParsedData as $row) {
        $tmpRow = [];
        foreach($parseHeader as $key => $val) {
          $tmpRow[$key] = $row[$val];
        }
        if(!empty($tmpRow)) {
          $parseData[] = $tmpRow;
        }
      }
    } else {
      //Parse body data
      if(!empty($this->limit)) {
        if(isset($this->limit['start']) && isset($this->limit['end'])) {
          $tmpParsedData = array_slice($this->parsedData, $this->limit['start'], $this->limit['end']);
        } else if(isset($this->limit['start'])) {
          $tmpParsedData = array_slice($this->parsedData, $this->limit['start']);
        }
        $parseData = $tmpParsedData;
      } else {
        $parseData = $this->parsedData;
      }
    }
    return $parseData;
  }

  /**
  * Get parsed data to object
  *
  * @param array $header
  * @return object
  */
  function toObject(array $header=NULL) : object {
    return (object) $this->toArray($header);
  }

  /**
  * Get parsed data to json
  *
  * @param array $header
  * @return string
  */
  function toJson(array $header=NULL) : string {
    return json_encode($this->toArray($header));
  }

  /**
  * Get parsed data to csv
  *
  * @param array $header
  * @return string
  */
  function toCsv(array $header=NULL) : string {
    $parseHeader = [];
    $parsedData = $this->toArray($header);
    $csvData = '';
    if(!empty($parsedData)) {
      if(!empty($header)) {
        //Check header is valid or not
        if(!empty($this->header)) {
          if($this->ignoreHeaderCase == true) {
            $ignoreHeaderCase = array_map('strtolower', $this->header);
            $tmpHeader = array_combine($ignoreHeaderCase, $this->header);
          } else {
            $tmpHeader = array_combine($this->header, $this->header);
          }
        }
        //Parse header data
        foreach($header as $col) {
          $tmpCol = $col;
          if($this->ignoreHeaderCase == true) {
            $col = strtolower($col);
          }
          if(!array_key_exists($col, $tmpHeader)) {
            //Throw error header not found
            throw new Exception("Error: '".$tmpCol."' header not found");
          } else {
            $parseHeader[$tmpCol] = $tmpHeader[$col];
          }
        }
      }
      if(!empty($parseHeader)) {
        $csvData .= implode($this->delimiter, $parseHeader).PHP_EOL;
      }
      foreach($parsedData as $row) {
        $csvData .= implode($this->delimiter, $row).PHP_EOL;
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
