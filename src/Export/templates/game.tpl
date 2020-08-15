{| class="wikitable" style="text-align: center
|-
! colspan="{{ data.headers|length }}" | {{ data.game.name }}
|-
! {{ data.headers|join(' !! ') }}
{% for columns in data.scores %}
|-
| {{ columns|join(' || ') }}
{% endfor %}
|}
