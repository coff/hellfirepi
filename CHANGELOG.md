# CHANGELOG

## dev-master

Vendor updated.

`PumpTrait`, `SensorArrayTrait` implemented to reduce code duplication.
  
`AirIntakeSystem` implemented as middle layer for servo control.

`DataSource` values Database storage implemented.

`Element` classes renamed to `System` and their further implementation done.

Bootstrapping implemented & pimple container setup.

Several bug-fixes.

Vendor updated.

Basic Command classes implemented.

`AnalogServo` class implemented for servo steering. 

`ComponentArray` classes implemented for managing arrays of DataSources.

`HellfireServer` prototype.

`Relay` prototype for relay control.

`CreateStorageComand`, `HellfireServerCommand` and `W1ServerCommand` prototypes.

Cleanups after extracting MAX6675, MCP3008, 1-wire code into separate
repositories (see composer.json for reference).  
