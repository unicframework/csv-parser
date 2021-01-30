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

class CSV {
  private $delimiter = ',';
  private $ignoreHeader = false;
  private $ignoreHeaderCase = true;
  private $headerOffset = 0;
  private $header = [];
  private $rowCount = 0;
  private $limit = [];
  private $parsedData = [];

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
          $fileData = file('data.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
          $tmpData = [];
          foreach($fileData as $row) {
            $tmpData[] = array_map('trim', explode($this->delimiter, $row));
          }
          $rawData = $tmpData;
        } else {
          //throw error
        }
      }
    } else {
      //throw error
    }
    //Parse data
    if(!empty($rawData)) {
      if($this->ignoreHeader === true) {
        $this->parsedData = $rawData;
      } else {
        //Parse header
        if(empty($this->header)) {
          if($this->headerOffset === 0) {
            $this->header = $rawData[0];
            //Remove header from data
            array_shift($rawData);
          } else if(isset($rawData[$this->headerOffset])) {
            $this->header = $rawData[$this->headerOffset];
            //Remove header from data
            unset($rawData[$this->headerOffset]);
          } else {
            //throw error header not found
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

  function setDelimiter(string $delimeter) {
    $this->delimiter = $delimiter;
  }

  function ignoreHeader(bool $ignore) {
    $this->ignoreHeader = $ignore;
  }

  function ignoreHeaderCase(bool $ignore) {
    $this->ignoreHeaderCase = $ignore;
  }

  function headerOffset(int $offset) {
    $this->headerOffset = $offset;
  }

  function setHeader(array $header) {
    $this->header = $header;
  }

  function rowCount() {
    return count($this->parsedData);
  }

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

  function toArray(array $header=NULL) {
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
          //throw error header not found
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
      $parseData = $this->parsedData;
    }
    return $parseData;
  }

  function toObject(array $header=NULL) {
    return (object) $this->toArray($header);
  }

  function toJson(array $header=NULL) {
    return json_encode($this->toArray($header));
  }

  function toCsv(array $header=NULL) {
    $parsedData = $this->toArray($header);
    $csvData = '';
    if(!empty($parsedData)) {
    if($this->ignoreHeader == false && !empty($header)) {
      $csvData .= implode($this->delimiter, $header).PHP_EOL;
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
  private function is_json($data) {
    return is_array($data) ? false : is_array(json_decode($data, true));
  }
}
?>
