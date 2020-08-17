<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>STG Hall of Records - MediaWiki Page Generator</title>
    <style>
      textarea {
          width: 800px;
          height: 200px;
      }
    </style>
  </head>

  <body>
    <h1>STG Hall of Records - MediaWiki Page Generator</h1>

    <h2>Input</h2>
    <form method="post">
      <p>Copy the contents of the wiki page located at /Database into the textbox below.</p>
      <div>
        <label for="locales">Locales</label>
        <input type="text" id="locales" name="locales" value="{{ locales }}" />
      </div>
      <div>
        <textarea
          name="input"
          placeholder="Input from database page"
          >{{ input }}</textarea>
      </div>
      <div>
        <input type="submit" value="Generate" />
      </div>
    </form>

    <h2>Output</h2>
    {{ error }}
    {% for page in output %}
    <h3>Output for locale {{ page.locale }}</h3>
    <div>
      <p>Copy the contents of this textbox into the wiki page located at /Database/{{ page.locale }}</p>
      <textarea>{{ page.output }}</textarea>
    </div>
    {% endfor %}

  </body>
</html>
