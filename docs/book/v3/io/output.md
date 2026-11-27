# Output data

`dot-maker` aims to deliver a simple and intuitive user experience.
Each user input can result in one of the following outputs:

- on success: the message is green and shows what file was created
- on failure: the message is red and shows what went wrong

## Debug info

On executing a create component command, the first output you will see is related to project detection.

The first line is an info message confirming the project type, for example:

> Detected project type: Api

The second line is an info message confirming whether the project uses Core architecture, for example:

> Core architecture: Yes

These are detected by looking at the modules registered in `composer.json` under **autoload**.**psr-4**.
At least one namespace that does not start with `Core\` must be registered there, otherwise `dot-maker` stops with an error.

## Next steps

After creating a component that needs manual registration (a Module, Command, Middleware or Entity), `dot-maker` prints a **Next steps** list with the tasks to perform, such as registering a Command in `config/autoload/cli.global.php`.

## Exit codes

- `0`: the command finished successfully
- `1`: the command failed, for example because of an unknown component or an invalid `composer.json`

Use the exit code when running `dot-maker` from scripts.
