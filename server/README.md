<p align="center"><a href="https://dreamfork.dream-speak.pl" target="_blank"><img src="https://raw.githubusercontent.com/PiciuU/DreamFork-PHP-Framework/master/resources/icons/logo.svg" width="200" alt="Dreamfork Logo"></a></p>

# PokeCrew API Server

PokeCrew website utilizes the [Dreamfork](https://github.com/PiciuU/DreamFork-PHP-Framework) framework as its backend API. The framework is specifically designed to handle API requests, as reflected in the `RouteServiceProvider` file.

### Configuration

Apart from the default framework settings in the `env` file, the following additional configurations are defined:

- **OVERRIDED_STORAGE_PATH**: If you wish to specify a custom location for storing files instead of the default `storage/app/public` folder, provide the desired path here.

#### General Purpose of the API

The application relies on a properly connected API for frontend functionality. Determine the API's accessible address and provide it in the client's configuration in the `client` folder. Assuming the server was launched using the command `php -S localhost:8000 -t public/`, the address would be http://localhost:8000/.

The API facilitates the transfer of files to the server, handling reception, validation, and storage operations. Its primary function revolves around file transfer and management.
