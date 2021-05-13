## CSVParser

<p align="center">
  <img src="logo.jpg" width="400px" alt="Unic Logo">
</p>

  CSVParser library parse csv data to array, object, and json format. CSVParser convert array, object, json to csv format.


### Features

- Parse data from csv file, array, json and objects.
- Parse csv to array and array to csv.
- Parse csv to object and object to csv.
- Parse csv to json and json to csv.
- Set custom headers to csv file.


### Installation

  - Install `composer` if you have not installed.

```shell
composer require unicframework/csv-parser
```

### Parse Data

```php
use CSVParser\CSV;

$csv = new CSV();

//Parse data from csv file
$csv->parse('data.csv');

//Parse array data
$csv->parse($arrayData);

//Parse object data
$csv->parse($jsonData);

//Parse json data
$csv->parse($objectData);
```


### Get Parsed Data

```php
//Get header
$header = $csv->getHeader();

//Get parsed data to array format
$data = $csv->toArray();

//Select data from parsed data
$data = $csv->toArray(['Name', 'Email']);

//Get parsed data to object format
$data = $csv->toObject();

//Select data from parsed data
$data = $csv->toObject(['Name', 'Email']);

//Get parsed data to json format
$data = $csv->toJson();

//Select data from parsed data
$data = $csv->toJson(['Name', 'Email']);

//Get parsed data to csv format
$data = $csv->toCsv();

//Select data from parsed data
$data = $csv->toCsv(['Name', 'Email']);
```

### Get Row Count

```php
//Get row count
$rows = $csv->rowCount();

//Get header count
$cols = $csv->headerCount();
```

### Select Data Limit

```php
//Select 10 records
$csv->limit(10);
$data = $csv->toArray();

//Select from 5 to 10 records
$csv->limit(5, 10);
$data = $csv->toArray();
```


### Set CSV Header

```php
//Ignore header from csv file
$csv->ignoreHeader(true);

//Ignore csv header cse
$csv->ignoreHeaderCase(true);

//Ignore csv enclosure
$csv->ignoreEnclosure(true);

//Set header offset of csv file
$csv->headerOffset(0);

//Set custom header to csv file
$csv->setHeader(['Name', 'Email']);
```


### Set CSV Delimiter

  Default csv delimiter is `,` but we can set other delimiter for csv file.

```php
//Set delimiter
$csv->setDelimiter('|');

//Set enclosure
$csv->setEnclosure('"');

//Set escape character
$csv->setEscape('//');

//Parse csv file data
$csv->parse('data.csv');

//Get data from parsed data
$data = $csv->toArray();
```


### Aggregate Functions

```php

//Parse csv file data
$csv->parse('data.csv');

//Get total sum of given field
$total_price = $csv->sum('price');

//Get minimum from given field
$min_price = $csv->min('price');

//Get maximum from given field
$max_price = $csv->max('price');

//Get average of given field
$average_price = $csv->average('price');
```

## License

  [MIT License](https://github.com/unicframework/csv-parser/blob/main/LICENSE)
