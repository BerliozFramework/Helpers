# Change Log

All notable changes to this project will be documented in this file. This project adheres
to [Semantic Versioning] (http://semver.org/). For change log format,
use [Keep a Changelog] (http://keepachangelog.com/).

## [1.11.1] - 2025-03-27

### Fixed

- Missing `b_array_nested()` bootstrap function

## [1.11.0] - 2025-03-27

### Added

- New method `ArrayHelper::nestedArray()` to transform simple array into multidimensional array

### Changed

- Complex paths allowed for `ArrayHelper::traverse*` methods

## [1.10.0] - 2025-03-14

### Changed

- PHP 8.4 compatibility

## [1.9.0] - 2022-10-26

### Added

- New method `ArrayHelper::simpleArray()` to simplify a multidimensional array

## [1.8.0] - 2022-03-18

### Added

- New method `FileHelper::ftruncate()` to truncate a part of file and shift rest of data.

## [1.7.0] - 2022-03-17

### Added

- New method `FileHelper::fwritei()` to write with insertion of content instead of replacement

## [1.6.5] - 2022-03-07

### Fixed

- `StringHelper::parseStr()` missing decode value in case of variable starts with brackets

## [1.6.4] - 2022-03-07

### Fixed

- `StringHelper::parseStr()` with variable names starts with brackets

## [1.6.3] - 2022-03-04

### Fixed

- `FileHelper::resolveAbsolutePath()` confuse directory of 2 characters with '..'

## [1.6.2] - 2022-02-04

### Fixed

- `FileHelper::resolveAbsolutePath()` with empty destination path

## [1.6.1] - 2022-01-18

### Fixed

- `StringHelper::parseStr()` with encoded brackets

## [1.6.0] - 2022-01-10

### Added

- New method `ArrayHelper::column()` to do similar job than `array_column()` native function but accepts \Closure in
  arguments to found keys

### Changed

- Method `ArrayHelper::isSequential()` becomes deprecated (`b_array_is_sequential()` function),
  use `ArrayHelper::isList()` instead (`b_array_is_list()` function)

## [1.5.1] - 2021-12-23

### Fixed

- Resolution of directory paths

## [1.5.0] - 2021-12-22

### Added

- New method `FileHelper::resolveAbsolutePath()` to resolve absolute path from another
- New method `FileHelper::resolveRelativePath()` to resolve relative path from another

## [1.4.0] - 2021-12-07

### Changed

- `ArrayHelper::isSequential()` use `array_is_list()` function in PHP 8.1

### Fixed

- `ArrayHelper::isSequential()` return TRUE only if keys are sequential INTEGER
- `StringHelper::parseStr()` with empty string or empty key

## [1.3.1] - 2021-10-18

### Fixed

- Fix not decoded variable name with `StringHelper::parseStr()`

## [1.3.0] - 2021-10-18

### Added

- New method `StringHelper::parseStr()` which keep dots in variable name

## [1.2.0] - 2021-05-03

### Changed

- `ArrayHelper::mergeRecursive()` accepts no parameters
- `ArrayHelper::traverse*()` have typed `iterable` first parameter

### Fixed

- Array merge with empty arrays

## [1.1.5] - 2021-04-25

### Changed

- Bump PHPUnit version to 9.3

### Fixed

- Cast parameters given to `imagecopyresampled` function to integer

## [1.1.4] - 2021-04-02

### Fixed

- Fixed ArrayHelper::traverseHas() not returning true for null value
- Fixed ArrayHelper::traverseGet() not returning a null value

## [1.1.3] - 2021-03-31

### Fixed

- Fixed ArrayHelper::traverseGet() not returning any default value on a non-existent final key
- Fixed ArrayHelper::traverseSet() not set value on a non-existent final key

## [1.1.2] - 2021-03-11

### Fixed

- ArrayHelper::traverseExists() returns true on non-existent final key

## [1.1.1] - 2020-11-05

### Added

- PHP 8 compatibility in `composer.json`

# Changed

- Bump PHP compatibility to 7.3

## Fixed

- StringHelper::removeAccents() returns empty string if error
- Bad image resize for portrait/landscape ratio
- ImageHelperTest::providerSizes() parameters name
- Deprecated assertRegExp() and assertNotRegExp() methods
- Cast value given to dechex() function in ImageHelper::gradientColor() method

## [1.1.0] - 2020-07-30

### Added

- Add support of GdImage class in PHP 8

### Changed

- Simplify FQN in sources

## [1.0.0] - 2020-02-17

First version
