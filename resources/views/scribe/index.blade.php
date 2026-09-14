<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Laravel API Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "http://localhost:8000";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.11.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.11.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-support-tickets" class="tocify-header">
                <li class="tocify-item level-1" data-unique="support-tickets">
                    <a href="#support-tickets">Support Tickets</a>
                </li>
                                    <ul id="tocify-subheader-support-tickets" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="support-tickets-GETapi-tickets">
                                <a href="#support-tickets-GETapi-tickets">List Tickets</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="support-tickets-GETapi-tickets--id-">
                                <a href="#support-tickets-GETapi-tickets--id-">Get Single Ticket</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-ticket-escalation" class="tocify-header">
                <li class="tocify-item level-1" data-unique="ticket-escalation">
                    <a href="#ticket-escalation">Ticket Escalation</a>
                </li>
                                    <ul id="tocify-subheader-ticket-escalation" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="ticket-escalation-POSTapi-tickets--id--escalate">
                                <a href="#ticket-escalation-POSTapi-tickets--id--escalate">Escalate a Ticket</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: September 14, 2026</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<aside>
    <strong>Base URL</strong>: <code>http://localhost:8000</code>
</aside>
<pre><code>This documentation aims to provide all the information you need to work with our API.

&lt;aside&gt;As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).&lt;/aside&gt;</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>This API is not authenticated.</p>

        <h1 id="support-tickets">Support Tickets</h1>

    <p>APIs for querying support tickets and tracking incidents.</p>

                                <h2 id="support-tickets-GETapi-tickets">List Tickets</h2>

<p>
</p>

<p>Retrieves paginated support tickets along with customer and agent relationships.</p>

<span id="example-requests-GETapi-tickets">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/tickets" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/tickets"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-tickets">
            <blockquote>
            <p>Example response (200, Tickets list):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;success&quot;: true,
    &quot;status&quot;: 200,
    &quot;message&quot;: &quot;Tickets retrieved successfully.&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;subject&quot;: &quot;Payment Gateway Returning 502 Bad Gateway&quot;,
            &quot;status&quot;: &quot;open&quot;,
            &quot;status_label&quot;: &quot;Open&quot;,
            &quot;priority&quot;: &quot;urgent&quot;,
            &quot;priority_label&quot;: &quot;Urgent&quot;,
            &quot;is_escalatable&quot;: true,
            &quot;customer&quot;: {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Acme Corporation&quot;,
                &quot;email&quot;: &quot;support@acme.corp&quot;
            }
        }
    ],
    &quot;pagination&quot;: {
        &quot;total&quot;: 6,
        &quot;per_page&quot;: 15,
        &quot;current_page&quot;: 1,
        &quot;last_page&quot;: 1
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-tickets" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-tickets"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-tickets"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-tickets" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-tickets">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-tickets" data-method="GET"
      data-path="api/tickets"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-tickets', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-tickets"
                    onclick="tryItOut('GETapi-tickets');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-tickets"
                    onclick="cancelTryOut('GETapi-tickets');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-tickets"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/tickets</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-tickets"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-tickets"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="support-tickets-GETapi-tickets--id-">Get Single Ticket</h2>

<p>
</p>

<p>Fetches detailed ticket records, including customer profile, assigned agent,
escalation history records, and notification delivery attempt logs.</p>

<span id="example-requests-GETapi-tickets--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/tickets/1" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/tickets/1"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-tickets--id-">
            <blockquote>
            <p>Example response (200, Ticket details):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;success&quot;: true,
    &quot;status&quot;: 200,
    &quot;message&quot;: &quot;Ticket retrieved successfully.&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: 1,
        &quot;subject&quot;: &quot;Payment Gateway Returning 502 Bad Gateway&quot;,
        &quot;description&quot;: &quot;Customers cannot checkout during peak traffic hours.&quot;,
        &quot;status&quot;: &quot;escalated&quot;,
        &quot;status_label&quot;: &quot;Escalated&quot;,
        &quot;priority&quot;: &quot;urgent&quot;,
        &quot;priority_label&quot;: &quot;Urgent&quot;,
        &quot;is_escalatable&quot;: false,
        &quot;customer&quot;: {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Acme Corporation&quot;,
            &quot;email&quot;: &quot;support@acme.corp&quot;
        },
        &quot;escalations&quot;: [],
        &quot;notification_logs&quot;: []
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (404, Ticket not found):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;success&quot;: false,
    &quot;status&quot;: 404,
    &quot;message&quot;: &quot;Ticket #999999 not found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-tickets--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-tickets--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-tickets--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-tickets--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-tickets--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-tickets--id-" data-method="GET"
      data-path="api/tickets/{id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-tickets--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-tickets--id-"
                    onclick="tryItOut('GETapi-tickets--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-tickets--id-"
                    onclick="cancelTryOut('GETapi-tickets--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-tickets--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/tickets/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-tickets--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-tickets--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-tickets--id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the ticket. Example: <code>1</code></p>
            </div>
                    </form>

                <h1 id="ticket-escalation">Ticket Escalation</h1>

    <p>APIs for managing ticket escalation workflows and triggering incident alerts.</p>

                                <h2 id="ticket-escalation-POSTapi-tickets--id--escalate">Escalate a Ticket</h2>

<p>
</p>

<p>Escalates an open or in-progress ticket, sets its status to "escalated",
records the escalation timestamp and reason, and automatically dispatches
multi-channel notifications (Email &amp; Slack) with up to 3 automatic retries.</p>
<p>If an authenticated user is present (e.g. via Sanctum), they are automatically
recorded as the escalator. Otherwise, <code>escalated_by</code> can be optionally supplied.</p>

<span id="example-requests-POSTapi-tickets--id--escalate">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/tickets/1/escalate" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"reason\": \"Production payment gateway SLA breached. VIP customer affected.\",
    \"escalated_by\": 1
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/tickets/1/escalate"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "reason": "Production payment gateway SLA breached. VIP customer affected.",
    "escalated_by": 1
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-tickets--id--escalate">
            <blockquote>
            <p>Example response (200, Successful escalation):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;success&quot;: true,
    &quot;status&quot;: 200,
    &quot;message&quot;: &quot;Ticket escalated successfully. Notifications dispatched.&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: 1,
        &quot;subject&quot;: &quot;Payment Gateway Returning 502 Bad Gateway&quot;,
        &quot;description&quot;: &quot;Transactions are dropping during peak hours.&quot;,
        &quot;status&quot;: &quot;escalated&quot;,
        &quot;status_label&quot;: &quot;Escalated&quot;,
        &quot;priority&quot;: &quot;urgent&quot;,
        &quot;priority_label&quot;: &quot;Urgent&quot;,
        &quot;is_escalatable&quot;: false,
        &quot;customer&quot;: {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Acme Corporation&quot;,
            &quot;email&quot;: &quot;support@acme.corp&quot;
        },
        &quot;escalated_at&quot;: &quot;2026-09-14T15:10:00.000000Z&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (404, Ticket not found):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;success&quot;: false,
    &quot;status&quot;: 404,
    &quot;message&quot;: &quot;Ticket #999999 not found.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, Ticket is already escalated, closed, or resolved):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;success&quot;: false,
    &quot;status&quot;: 422,
    &quot;message&quot;: &quot;Ticket #1 cannot be escalated because its status is &#039;escalated&#039;. Only open or in-progress tickets may be escalated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-tickets--id--escalate" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-tickets--id--escalate"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-tickets--id--escalate"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-tickets--id--escalate" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-tickets--id--escalate">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-tickets--id--escalate" data-method="POST"
      data-path="api/tickets/{id}/escalate"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-tickets--id--escalate', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-tickets--id--escalate"
                    onclick="tryItOut('POSTapi-tickets--id--escalate');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-tickets--id--escalate"
                    onclick="cancelTryOut('POSTapi-tickets--id--escalate');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-tickets--id--escalate"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/tickets/{id}/escalate</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-tickets--id--escalate"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-tickets--id--escalate"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="POSTapi-tickets--id--escalate"
               value="1"
               data-component="url">
    <br>
<p>The ID of the ticket to escalate. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>reason</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="reason"                data-endpoint="POSTapi-tickets--id--escalate"
               value="Production payment gateway SLA breached. VIP customer affected."
               data-component="body">
    <br>
<p>Optional business reason explaining the need for escalation. Must not be greater than 1000 characters. Example: <code>Production payment gateway SLA breached. VIP customer affected.</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>escalated_by</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="escalated_by"                data-endpoint="POSTapi-tickets--id--escalate"
               value="1"
               data-component="body">
    <br>
<p>Optional User/Agent ID performing the escalation. If authenticated via Sanctum, defaults to the authenticated user ID. Must match an existing stored value. Example: <code>1</code></p>
        </div>
        </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                            </div>
            </div>
</div>
</body>
</html>
