{% set gameName = null %}
{% set gameMessages = [] %}

<messages>
{% for message in messages %}
    {% if message.context.game %}
        {% if message.context.game == gameName %}
            {% set gameMessages = gameMessages|merge([message]) %}
        {% else %}
            {% if gameMessages %}
                {% include('media-wiki-image-scraper--game-output.tpl') %}
            {% endif %}
            {% set gameName = message.context.game %}
            {% set gameMessages = [message] %}
        {% endif %}
    {% else %}
        {% if gameMessages %}
            {% include('media-wiki-image-scraper--game-output.tpl') %}
        {% endif %}
        {% set gameMessages = [] %}
        <message class="general">{{ message.message }}</message>
    {% endif %}
{% endfor %}
{% if gameMessages %}
    {% include('media-wiki-image-scraper--game-output.tpl') %}
{% endif %}
</messages>
