# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.6] - 2018-10-19

### Changed
- Added a $oneLevel parameter to search to be able to do one level searching instead of full deep search. Default is false, so the search will behave the same as before, instead you specifically asks for one level search in the 4th parameter.


## [1.0.5] - 2018-10-19
### Added
- CHANGELOG.md
- cleanUpEntry function

### Changed
- Changed recursiveDelete search to only return dn attributes
