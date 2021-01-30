## CSVParser Library

  CSVParser library parse csv data to array, object, and json format. CSVParser convert array, object, json to csv format.


### Installation

  - Install `composer` if you have not installed.

```shell
composer require unicframework/csv-parser
```

### Parse data from csv file.

```php
use CSVParser\CSV;

$csv = new CSV();

//Parse csv file data
$csv->parse('data.csv');
```

### Parse data from array

```php
//Parse array data
$csv->parse($arrayData);
```

### Parse json data

```php
//Parse json data
$csv->parse($jsonData);
```

### Parse form object

```php
//Parse json data
$csv->parse($objectData);
```


### Get data in array format

```php
//Get parsed data to array format
$data = $csv->toArray();

//Select data from parsed data
$data = $csv->toArray(['Name', 'Email']);
```

### Get data in object format

```php
//Get parsed data to object format
$data = $csv->toObject();

//Select data from parsed data
$data = $csv->toObject(['Name', 'Email']);
```

### Get data in json format

```php
//Get parsed data to json format
$data = $csv->toJson();

//Select data from parsed data
$data = $csv->toJson(['Name', 'Email']);
```

### Get data in csv format

```php
//Get parsed data to csv format
$data = $csv->toObject();

//Select data from parsed data
$data = $csv->toCsv(['Name', 'Email']);
```

### Get row count

```php
//Get rowCount
$rows = $csv->rowCount();
```

### Select data limit

```php
//Select 10 records
$csv->limit(10);
$data = $csv->toArray();

//Select from 5 to 10 records
$csv->limit(5, 10);
$data = $csv->toArray();
```


### CSV Header

```php
//Ignore csv header
$csv->IgnoreHeader(true);

//Ignore csv header cse
$csv->ignoreHeaderCase(true);

//Set csv header offset
$csv->headerOffset(0);
```


### CSV delimiter

  Default csv delimiter is `,` but we can set other delimiter for csv file.

```php
//Set delimiter
$csv->setDelimiter('|');

//Parse csv file data
$csv->parse('data.csv');

//Get data from parsed data
$data = $csv->toArray();
```

## License

  [MIT License](https://github.com/unicframework/csv-parser/blob/main/LICENSE)
