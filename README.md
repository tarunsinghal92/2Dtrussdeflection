# 2D Truss Deflection Calculation

This is simple program which can be used to find deflection in 2D trusses. 

## Dependency

This project requires PHP 7+.


## Installation

Please follow the following steps in order to start the project.

* Create a clone wherever you want.

>$ git clone https://github.com/tarunsinghal92/2Dtrussdeflection

* Install the required dependencies.

>$ composer install

* Modify the values of young modulus, etc in config.inc file which is present in includes folder.

```php
/**
 * Young modulus 
 */
define('E', '30000');

/**
 * Area of cross-section for each member
 * assumed same to all members
 */
define('A', '10');

/**
 * magnifying factor of displacements
 */
define('MAGNIFICATION_FACTOR', 20.0);

/**
 * length amplification factor for unit conversion
 */
define('LENGTH_FACTOR', 1.0);

/**
 * magnifying factor in general
 */
define('GENERAL_MAGNIFICATION_FACTOR', 50.0);
```
* Modifiy the forces and Member details in data/members.txt and data/forces.txt file.

### Format for members.txt

```
element           : # of element
node1             : value of force in X-Dir(KN)
node2             : value of force in Y-Dir(KN) 
node1_constraints : DOF free/fixed @ Node1 in X-Dir,Y-Dir
node2_constraints : DOF free/fixed @ Node2 in X-Dir,Y-Dir

e.g. 1-2	0,0	2,0	fixed,free	free,free 
It means Node1 @ (0,0) is fixed in X-Dir and free in Y-Dir & 
Node2 @ (2,0) is free in both Dir for Element '1-2'
```
### Format for forces.txt
```
node : # of node
x    : value of force in X-Dir(KN)
y    : value of force in Y-Dir(KN) 

e.g. 1  100 200 
It means a force of 100KN in X-Dir & 200KN in Y-Dir is applied on Node #1
```

## Running the Program

Run following command in `terminal`.

>$ ./run.sh

Then navigate to http://localhost:8100 to view the results.

## License

This project is licensed under `MIT` License. See LICENSE for full license text.
