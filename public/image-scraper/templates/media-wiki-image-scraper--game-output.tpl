<game id="{{ gameName }}">
    <game-name>{{ gameName }}</game-name>

{% set scoreName = null %}
{% set scoreMessages = [] %}

{% for gameMessage in gameMessages %}
    {% if gameMessage.context.score %}
        {% if gameMessage.context.score == scoreName %}
            {% set scoreMessages = scoreMessages|merge([gameMessage]) %}
        {% else %}
            {% if scoreMessages %}
                {% include 'media-wiki-image-scraper--score-output.tpl' %}
            {% endif %}
            {% set scoreName = gameMessage.context.score %}
            {% set scoreMessages = [gameMessage] %}
        {% endif %}
    {% else %}
        {% if scoreMessages %}
            {% include 'media-wiki-image-scraper--score-output.tpl' %}
        {% endif %}
        {% set scoreMessages = [] %}
        <game-message>{{ gameMessage.message }}</game-message>
    {% endif %}
{% endfor %}

{% if scoreMessages %}
    {% include 'media-wiki-image-scraper--score-output.tpl' %}
{% endif %}
</game>
