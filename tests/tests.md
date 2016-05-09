#transfer/ezplatform test guidance

## Integration tests

* Should run find action via createAndUpdate on all (a) object- and (b) tree services

* Should run create action via createAndUpdate on all (a) object- and (b) tree services

* Should run update action via createAndUpdate on all (a) object- and (b) tree services

* Should run delete action via createAndUpdate on all (a) object- and (b) tree services

a) Objectservices
* ContentManager
* LocationManager
* LanguageManager
* ContentTypeManager
* UserManager
* UserGroupManager
    
b) TreeServices
* ContentTreeService

## Unit tests

* Should cover all find() with ID (not Remote Id)
* Should cover all exceptions, and false-returns
