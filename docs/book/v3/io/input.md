# Input data

`dot-maker` uses two types of input:

- prompt: where the user has to enter some data and press **Enter**
- confirm: where the user has to enter one of the predefined options and/or press **Enter**

## Prompt

The user is presented with a message, for example:

> Input file name:

and they must enter some data and press **Enter**.

Whether the command accepts blank input depends on the command.
Some will accept blank input and exit, others will keep prompting until the user enters some valid data.

## Confirm

> Confirmations are case-insensitive, so the user may enter lowercase, uppercase or mixed-case letters.

The user is presented with a message, for example:

> Perform an action? [Y(es)/n(o)]:

OR

> Perform an action? [y(es)/N(o)]:

and they must enter one of the predefined options and/or press **Enter**.

The valid options are: `y`, `yes`, `n`, `no` and blank.
If the user enters an invalid value, the command will loop until it receives a valid input.

If the user submits a blank input, the default value is used.
The default value can be identified by finding the branch starting with a capital letter.
So, for `[Y(es)/n(o)]` the default value is `yes` and for `[y(es)/N(o)]` the default value is `no`.

## End of input

When the input ends (for example after **Ctrl+D**, or when answers are piped in and run out), every remaining prompt receives a blank answer:

- a prompt that accepts blank input exits, as described above
- a confirmation uses its default value
- the **Existing module name** prompt, which needs an answer, stops the run with this error and exit code 1:

> No module name given before the end of input.
