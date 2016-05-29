#transfer/ezplatform test guidance

## Integration tests

* Should run find action via createAndUpdate on all (a) object- and (b) tree services

* Should run create action via createAndUpdate on all (a) object- and (b) tree services

* Should run update action via createAndUpdate on all (a) object- and (b) tree services

* Should run delete action via createAndUpdate on all (a) object- and (b) tree services


## Unit tests

* Should cover all manager->find() calls where ID (not Remote Id) is used
* Should cover all exceptions not covered by integration tests
* Should cover all mappers for corresponding (c) EzPlatformObject not covered by integration tests
* Should cover all standalone workers

a) Objectservices
* ContentManager
* LocationManager
* LanguageManager
* ContentTypeManager
* UserManager
* UserGroupManager

b) TreeServices
* ContentTreeService

c) EzPlatformObjects
* ContentObject
* ContentTypeObject
* LocationObject
* LanguageObject
* UserObject
* UserGroupObject