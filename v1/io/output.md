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
