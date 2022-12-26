import json, xmltodict

xml = open('countries.xml', encoding="utf8").read();
dict = xmltodict.parse(xml)['countries_states_cities']['country_state_city']

countries = {}
for country in dict:
  states = {}
  if(country['states']):
    for state in country['states']:
      cities = []
      if(state['cities']):
        for city in state['cities']:
          try:
            cities.append(city['name'])
          except:
            print(city)
      else:
        print(state['cities'])
      states[state['name']] = {
        'name': state['name'],
        'cities': cities,
      }
  else:
    print(country['states'])
  countries[country['name']] = {
    'name': country['name'],
    'states': states,
  }

open('c.json', 'w', encoding='utf8').write(json.dumps(countries, indent=2, ensure_ascii=False))