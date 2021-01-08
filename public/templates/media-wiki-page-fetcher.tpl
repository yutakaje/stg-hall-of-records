<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>STG Hall of Records - MediaWiki Page Fetcher</title>
    <style>
      body {
          background-color: #EEEEEE;
          margin: 20px;
      }
      li {
          margin-bottom: 5px;
      }
      .error {
          color: #c81717;
      }
    </style>
  </head>

  <body>
    <h1>MediaWiki Page Fetcher</h1>

    <div class="error">{{ error }}</div>

    <h2>Overview</h2>
    <p>
      This tool fetches the contents of a wiki page and optionally all referenced files as well (usually images).<br/>
      It does <b>not</b> fetch the HTML output but the wiki source data (the contents you enter when you edit a page).
    </p>
    <p>
      If you just fetch the wiki page itself (without its referenced files), you will get a text file with its contents.<br/>
      If you fetch the referenced files as well, you will get a zip file with all the contents.
    </p>

    <br/>

    <h2>STG Hall of Records pages</h2>

    <ul>
      <li><a href="?page=database">Download HoR database</a></li>
      <li><a href="?page=page-en">Download English HoR page</a></li>
    </ul>

    <br/>

    <h2>Link generator</h2>

    <p>
      Enter the name of the wiki page you want to fetch.<br/>
      If you want the referenced files (usually images) included, mark the checkbox as well.
    </p>

    <div>
      <label for="page">Page name</label>
      <input type="text" id="page" name="page" value="" placeholder="STG_Hall_of_Records"/>
    </div>
    <div>
      <label for="includeFiles">Include files</label>
      <input type="checkbox" id="includeFiles" name="includeFiles" value="true" />
    </div>
    <br/>
    <div>
      <button onclick="hor_generateLink()">Generate link</button>
    </div>

    <br/>
    <hr/>

    <div id="output" />

    <script>
      function hor_generateLink() {
          var page = document.getElementById('page').value;
          if (!page) {
              alert('Please specify a page name');
              return;
          }

          var url = window.location.protocol + '//' + window.location.host
              + window.location.pathname + '?page=' + page;

          if (document.getElementById('includeFiles').checked) {
              url += '&includeFiles=true';
          }

          var link = document.createElement('a');
          link.setAttribute('href', url);
          link.appendChild(document.createTextNode(url));

          var output = document.getElementById('output');
          output.appendChild(link)
          output.appendChild(document.createElement('br'));
      }
    </script>
</body>
</html>
