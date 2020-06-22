# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.11] - 2020-06-22

### Changed
- Fixed php 7.4 array error

## [1.0.11] - 2019-02-04

### Changed
- Renamed class to meet psr-4 standards

## [1.0.7] - 2019-02-04

### Changed
- Removed unused controller
- Removed Session::flash (should be handled outside of package)
- Removed logging when new record is succesfully added

## [1.0.6] - 2018-10-19

### Changed
- Added a $oneLevel parameter to search to be able to do one level searching instead of full deep search. Default is false, so the search will behave the same as before, instead you specifically asks for one level search in the 4th parameter.


## [1.0.5] - 2018-10-19
### Added
- CHANGELOG.md
- cleanUpEntry function

### Changed
- Changed recursiveDelete search to only return dn attributes
