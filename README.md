# MyQ test Cleaning robot

## Requirements
- PHP 7.3 or higher
- Composer

***No webserver installation is required.***

## Installation
- Clone or copy this repo to the new project directory.
- Go to command line (cmd), change to the project directory and run `composer install` to install all the dependencies
- Start application from command line. Examples:
```shell
c:\PROJECT_FOLDER>php run cleaner -h
```
Display command line options and other information about application
```shell
c:\PROJECT_FOLDER>php run cleaner files/testmyq1.json files/result.json
```
Run robot with the program configured in **testmyq1.json** file.
Log of all robot movements and other related information will be shown on the screen.
```shell
c:\PROJECT_FOLDER>php run cleaner files/testmyq1.json files/testmyq1_result.json --logfile files/testmyq1_log.json
```
Run robot with the command sequence configured in **testmyq1.json** file.
Log of all robot movements and other related information will be output to the log file **testmyq1_log1.json**.

## Unit tests 
Start unit testing by running this command in the command line
```shell
c:\PROJECT_FOLDER>php vendor/bin/phpunit tests/Test.php
```
Look at examples of inputs, outputs and logs in the **files** folder.

Technical requirement for the application can be found in the **MyQ Unattended Coding Test 2.5** file.