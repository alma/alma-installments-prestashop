Authentication documentation
===================================

## Context
The API key configuration is a crucial part of the authentication process for our module. It allows merchants to securely connect their module with their Alma Account.
Proper validation, encryption, and consistency checks are essential to ensure the security and integrity of the API keys.

## Link to the documentation
[Linear Ticket](https://linear.app/almapay/issue/ECOM-3502/authentication)

## User Story
As a merchant, I want to be able to securely fulfill the API key in the back office module and select the mode Live or Test.
Before the save we check with our API `v1/me` endpoint if the key is valid.
If the key is/are valid, we encrypt the key/keys and save it/them in the database, and we display a success message. If not we display an error message and we don't save it in the database.
On the return in the configuration page, the api keys will be replace by an obscure like `********` to hide the real value of the key, and the mode will be selected according to the value saved in the database.

If we save only one key, we select the mode according to the key saved in the database, and return a message to the merchant to inform him that only one key is saved and the mode is selected according to this key.

If we save two keys, we check both keys before saving them in the database, if one of the keys is not valid we don't save any key in the database and we return an error message to the merchant to inform him that the keys XXX must be valid to be saved in the database.

If we save two keys with different merchant id, we don't save any key in the database and we return an error message to the merchant to inform him that the keys must have the same merchant id to be saved in the database.

We save also the merchant id concerning the API key in the configuration field and return it in an input disabled in the configuration page to inform the merchant about the merchant id of the API key saved in the database.
