# 2D Truss Deflection Calculation

Deflection in 2D truss 

## Dependency

This project requires PHP 7+.


## Installation

Please follow the following steps in order to start the project.

1. Create a clone wherever you want.

>$ git clone https://github.com/tarunsinghal92/2Dtrussdeflection

2. Install the required dependencies.

>$ composer install

3. Modify the values of young modulus, etc in config.inc file which is present in includes folder.

/**
 * Young modulus
 * assumed same to all members
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

4. Modifiy the forces and Member details in data/members.txt and data/forces.txt file.

### Format for members.txt

element 	node1	node2	node1_constraints	node2_constraints
1-2	0,0	2,0	fixed,fixed	free,free
2-3	2,0	4,0	free,free	free,free
3-4	4,0	6,0	free,free	free,free

element           : # of element
node1             : value of force in X-Dir(KN)
node2             : value of force in Y-Dir(KN) 
node1_constraints :
node2_constraints : 

e.g. 1-2	0,0	2,0	fixed,free	free,free (meaning Node1 @ (0,0) is fixed in X-Dir and free in Y-Dir & Node2 is free in both Dir for Element '1-2')

### Format for forces.txt
 
node : # of node
x    : value of force in X-Dir(KN)
y    : value of force in Y-Dir(KN) 

e.g. 1  100 200 (meaning 100KN in X & 200KN in Y on Node #1)


## Running the Program

Run following command in `terminal`.

>$ ./run.sh

## License

This project is licensed under `MIT` License. See LICENSE for full license text.
