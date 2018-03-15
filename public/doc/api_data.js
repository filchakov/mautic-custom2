define({ "api": [
  {
    "type": "post",
    "url": "/webhooks/lead",
    "title": "Create new contact",
    "name": "new_lead",
    "group": "User",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "allowedValues": [
              "\"http://example.com\"",
              "\"example.com\""
            ],
            "optional": false,
            "field": "project_url",
            "description": "<p>Project's URL (Example: http://dev.webscribble.com or dev.webscribble.com)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "firstname",
            "description": "<p>First name</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "lastname",
            "description": "<p>Last name</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "email",
            "description": "<p>Email address (Example: lead@company.com)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "allowedValues": [
              "\"job_seeker\"",
              "\"member\""
            ],
            "optional": false,
            "field": "type_account",
            "description": "<p>Type account. This field supports multiple types, with delimiter &quot;,&quot;. For example, if you want to create contact with both types, you have to fill this field like &quot;job_seeker, member&quot;. If you will provide some other word (than &quot;job_seeker&quot;, &quot;member&quot;) in this string - this tag will be ignored. You can use only tags &quot;job_seeker&quot;, &quot;member&quot; in this field.</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "phone",
            "description": "<p>Phone number</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "website",
            "description": "<p>Website</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "city",
            "description": "<p>City</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "address1",
            "description": "<p>Address 1</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "address2",
            "description": "<p>Address 2</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "state",
            "description": "<p>State</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "country",
            "description": "<p>Country</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "zipcode",
            "description": "<p>Zip code</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Boolean",
            "allowedValues": [
              "true"
            ],
            "optional": false,
            "field": "status",
            "description": "<p>Status request</p>"
          },
          {
            "group": "Success 200",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>Lead profile information</p>"
          },
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "data.id",
            "description": "<p>Lead's ID</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "allowedValues": [
              "\"job_seeker\"",
              "\"member\""
            ],
            "optional": false,
            "field": "data.type_account",
            "description": "<p>Type account</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "data.firstname",
            "description": "<p>First name</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "data.lastname",
            "description": "<p>Last name</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "data.email",
            "description": "<p>Email address</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": true,
            "field": "data.phone",
            "description": "<p>Phone</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": true,
            "field": "data.website",
            "description": "<p>Website</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": true,
            "field": "data.city",
            "description": "<p>City</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": true,
            "field": "data.address1",
            "description": "<p>Address 1</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": true,
            "field": "data.address2",
            "description": "<p>Address 2</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": true,
            "field": "data.state",
            "description": "<p>State</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": true,
            "field": "data.country",
            "description": "<p>Country</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": true,
            "field": "data.zipcode",
            "description": "<p>Zip code</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "optional": false,
            "field": "ProjectNotFound",
            "description": "<p>The project URL was not found in projects database</p>"
          },
          {
            "group": "Error 4xx",
            "optional": false,
            "field": "EmptyField",
            "description": "<p>An error that appears while passing a request with an empty  field which has status &quot;required&quot;</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Error-Response:",
          "content": "\nHTTP/1.1 404 Not Found\n{\n    \"status\": false,\n    \"data\": {\n        \"project_url\": \"URL does not exist in a database\"\n    }\n}",
          "type": "json"
        },
        {
          "title": "Error-Response:",
          "content": "\nHTTP/1.1 400 Bad Request\n{\n    \"status\": false,\n    \"data\": {\n        \"FIELD_NAME\": \"DESCRIPTION\"\n    }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "app/Http/Controllers/WebhookController.php",
    "groupTitle": "User"
  }
] });
