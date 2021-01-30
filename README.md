## CSVParser Library

  CSVParser library parse csv data to array, object, and json format. CSVParser convert array, object, json to csv format.


### Installation

  - Install `composer` if you have not installed.

```shell
composer require unicframework/csv-parser
```

### Example

```php
use CSVParser\CSV;

$csv = new CSV();

//Parse csv file
$csv->parse('data.csv');

//Get data to array format
$data = $csv->toArray();

//Get data to object format
$data = $csv->toObject();

//Get data to object format
$data = $csv->toJson();

//Get data to csv format
$data = $csv->toCsv();
```

## License

  [MIT License](https://github.com/unicframework/csv-parser/blob/main/LICENSE)
