# Get License Line Chart | FluentCart Developer Docs

URL: https://dev.fluentcart.com/restapi/operations/licensing/get-license-chart.html

---


# GET Get License Line Chart​

GET /reports/license-chart Retrieve license data formatted for a line chart visualization.


## Authorizations​

ApplicationPasswords WordPress Application Passwords - Enter your WordPress username and application password in the format: username:application_password

Type API Key (header: Authorization) 
## Parameters​


### Query Parameters

params * Request parameters object containing filters, date range, and grouping

Type object Required params[startDate] * Start date for the report (ISO 8601 format)

Type string Required Example `"2024-01-01"`Format `"date"`params[endDate] * End date for the report (ISO 8601 format)

Type string Required Example `"2024-12-31"`Format `"date"`params[groupKey] * Grouping key for the chart (e.g., 'day', 'week', 'month')

Type string Required Example `"month"`params[filters] Filter criteria

Type object 
## Responses​

200 Successful response

Content-Type application/json Schema JSON JSON { } GET /reports/license-chart 
## Playground​

Authorization 📡 Interactive API Playground 🗑️ Clear Browser Credentials This is a live API playground where you can test endpoints and see real-time responses. Your credentials are saved in your browser and will persist across pages.

1. Enter your WordPress website domain in the Server URL field below
2. Add your Application Password credentials in the Authorization field
3. Fill in any required parameters or request body data
4. Click "Try it out" to execute the API request
5. View the real-time response from your API below

⚠️ Important: Use test sites only. Requests make permanent changes. We do not collect or store any data. Server URL Your WordPress website domain (without https://) Full URL: https://YourWebsite.com/wp-json/fluent-cart/v2 ApplicationPasswords Variables Key Value params * params[startDate] * params[endDate] * params[groupKey] * params[filters] Try it out 
## Samples​

cURL JavaScript PHP Python cURL 
```
curl 'https://{website}/wp-json/fluent-cart/v2/reports/license-chart?params%5BstartDate%5D=2024-01-01&params%5BendDate%5D=2024-12-31&params%5BgroupKey%5D=month' \
  --header 'Authorization: Authorization'
```

JavaScript 
```
fetch('https://{website}/wp-json/fluent-cart/v2/reports/license-chart?params%5BstartDate%5D=2024-01-01&params%5BendDate%5D=2024-12-31&params%5BgroupKey%5D=month', {
  headers: {
    Authorization: 'Authorization'
  }
})
```

PHP 
```
$ch = curl_init("https://{website}/wp-json/fluent-cart/v2/reports/license-chart?params%5BstartDate%5D=2024-01-01&params%5BendDate%5D=2024-12-31&params%5BgroupKey%5D=month");

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Authorization']);

curl_exec($ch);

curl_close($ch);
```

Python 
```
requests.get(
    "https://{website}/wp-json/fluent-cart/v2/reports/license-chart",
    headers={
      "Authorization": "Authorization"
    },
    params={
      "params[startDate]": "2024-01-01",
      "params[endDate]": "2024-12-31",
      "params[groupKey]": "month"
    }
)
```

Powered by [VitePress OpenAPI](https://github.com/enzonotario/vitepress-openapi)