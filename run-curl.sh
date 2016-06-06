curl -v -H "Content-Type: application/json" -d '{
  "version":"1.0",
  "session":{
    "new":true,
    "sessionId":"S1",
    "application":{
      "applicationId":"1234"
    },
    "user":{
      "userId":"AlanKent"
    }
  },
  "request":{
    "type": "IntentRequest",
    "requestId": "R1",
    "timestamp": "2015-05-13T12:34:56Z",
    "intent": {
      "name": "ReportOrderCount"
    }
  }
}' http://192.168.33.33/alexa/v1.0

exit 0

curl -v -H "Content-Type: application/json" -d '{
  "version":"1.0",
  "session":{
    "new":true,
    "sessionId":"S1",
    "application":{
      "applicationId":"1234"
    },
    "user":{
      "userId":"AlanKent"
    }
  },
  "request":{
    "type": "IntentRequest",
    "requestId": "R1",
    "timestamp": "2015-05-13T12:34:56Z",
    "intent": {
      "name": "CheckStoreStatus",
      "slots": {
        "foo": {
          "name": "foo",
          "value": "foo-value"
	}
      }
    }
  }
}' http://192.168.33.33/alexa/v1.0
