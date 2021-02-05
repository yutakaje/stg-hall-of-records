<score-entry class="{{ scoreMessage.context.type }}">
    <score-message>{{ scoreMessage.message }}</score-message>

    {% set contextEntries = scoreMessage.context|filter((v,k) => k not in ['game', 'score', 'type']) %}
    {% if contextEntries|length > 0 %}
        <score-context>
            {% for contextName,contextValue in contextEntries %}
                <context-name>{{ contextName }}</context-name>
                {% if contextName == 'url' %}
                    <context-value><a href="{{ contextValue }}">{{ contextValue }}</a></context-value>
                {% elseif contextName == 'image' %}
                    <context-value><a href="{{ "#{saveUrl}/#{contextValue}" }}">{{ contextValue }}</a></context-value>
                {% else %}
                    <context-value>{{ contextValue }}</context-value>
                {% endif %}
            {% endfor %}
            </ul>
        </score-context>
    {% endif %}
</score-entry>
