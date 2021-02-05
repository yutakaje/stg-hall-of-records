{% set scoreEntries = [] %}
{% set messageTypes = [] %}
{% for scoreMessage in scoreMessages %}
    {% set scoreEntries = scoreEntries|merge([
        include('media-wiki-image-scraper--score-entry-output.tpl')
    ]) %}
    {% set messageTypes = messageTypes|merge([scoreMessage.context.type]) %}
{% endfor %}

{% if 'error' in messageTypes %}
    {% set cssClass = 'error' %}
{% elseif 'success' in messageTypes %}
    {% set cssClass = 'success' %}
{% else %}
    {% set cssClass = 'info' %}
{% endif %}

<score>
    <score-name class="{{ cssClass }}">{{ scoreName|replace({(gameName): '', '/': ''}) }}</score-name>
    {{ scoreEntries|join|raw }}
</score>