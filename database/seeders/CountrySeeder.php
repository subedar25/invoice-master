<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $rows = <<<'DATA'
1	United States	USA	true
2	Afghanistan	AF	true
4	Algeria	DZ	true
5	American Samoa	AS	true
6	Andorra	AD	true
7	Angola	AO	true
8	Anguilla	AI	true
9	Antarctica	AQ	true
10	Antigua And Barbuda	AG	true
11	Argentina	AR	true
12	Armenia	AM	true
13	Aruba	AW	true
14	Australia	AU	true
15	Austria	AT	true
16	Azerbaijan	AZ	true
17	Bahamas	BS	true
18	Bahrain	BH	true
19	Bangladesh	BD	true
20	Barbados	BB	true
21	Belarus	BY	true
22	Belgium	BE	true
23	Belize	BZ	true
24	Benin	BJ	true
25	Bermuda	BM	true
26	Bhutan	BT	true
27	Bolivia	BO	true
28	Bosnia And Herzegovina	BA	true
29	Botswana	BW	true
31	Brazil	BR	true
32	British Indian Ocean Territory	IO	true
33	Virgin Islands	VG	true
34	Brunei Darussalam	BN	true
36	Burkina Faso	BF	true
37	Burundi	BI	true
39	Cameroon	CM	true
40	Canada	CA	true
41	Cape Verde	CV	true
42	Cayman Islands	KY	true
43	Central African Republic	CF	true
44	Chad	TD	true
45	Chile	CL	true
46	China	CN	true
49	Colombia	CO	true
50	Comoros	KM	true
51	Congo	CG	true
52	Cook Islands	CK	true
53	Costa Rica	CR	true
54	Cote D'Ivoire	CI	true
55	Croatia	HR	true
56	Cuba	CU	true
57	Cyprus	CY	true
58	Czech Republic	CZ	true
59	Denmark	DK	true
60	Djibouti	DJ	true
61	Dominica	DM	true
62	Dominican Republic	DO	true
64	Ecuador	EC	true
65	Egypt	EG	true
66	El Salvador	SV	true
67	Equatorial Guinea	GQ	true
68	Eritrea	ER	true
69	Estonia	EE	true
70	Ethiopia	ET	true
71	Falkland Islands (Malvinas)	FK	true
72	Faroe Islands	FO	true
73	Fiji	FJ	true
74	Finland	FI	true
75	France	FR	true
77	French Guiana	GF	true
78	French Polynesia	PF	true
80	Gabon	GA	true
81	Gambia	GM	true
82	Georgia	GE	true
83	Germany	DE	true
84	Ghana	GH	true
85	Gibraltar	GI	true
86	Greece	GR	true
87	Greenland	GL	true
88	Grenada	GD	true
89	Guadeloupe	GP	true
90	Guam	GU	true
91	Guatemala	GT	true
92	Guinea	GN	true
93	Guinea-Bissau	GW	true
94	Guyana	GY	true
95	Haiti	HT	true
97	Honduras	HN	true
98	Hong Kong	HK	true
99	Hungary	HU	true
100	Iceland	IS	true
101	India	IN	true
102	Indonesia	ID	true
103	Iraq	IQ	true
104	Ireland	IE	true
105	Islamic Republic Of Iran	IR	true
106	Israel	IL	true
107	Italy	IT	true
108	Jamaica	JM	true
109	Japan	JP	true
110	Jordan	JO	true
111	Kazakhstan	KZ	true
112	Kenya	KE	true
113	Kiribati	KI	true
115	Republic Of Korea	KR	true
116	Kuwait	KW	true
117	Kyrgyzstan	KG	true
118	Lao People'S Democratic Republic	LA	true
119	Latvia	LV	true
120	Lebanon	LB	true
121	Lesotho	LS	true
122	Liberia	LR	true
123	Libyan Arab Jamahiriya	LY	true
124	Liechtenstein	LI	true
125	Lithuania	LT	true
126	Luxembourg	LU	true
127	Macao	MO	true
128	The Former Yugoslav Republic Of Macedonia	MK	true
129	Madagascar	MG	true
130	Malawi	MW	true
131	Malaysia	MY	true
132	Maldives	MV	true
133	Mali	ML	true
134	Malta	MT	true
136	Martinique	MQ	true
137	Mauritania	MR	true
138	Mauritius	MU	true
139	Mayotte	YT	true
140	Mexico	MX	true
141	Federated States Of Micronesia	FM	true
142	Republic Of Moldova	MD	true
143	Monaco	MC	true
144	Mongolia	MN	true
145	Montserrat	MS	true
146	Morocco	MA	true
147	Mozambique	MZ	true
148	Myanmar	MM	true
149	Namibia	NA	true
150	Nauru	NR	true
151	Nepal	NP	true
152	Netherlands	NL	true
153	Netherlands Antilles	AN	true
154	New Caledonia	NC	true
155	New Zealand	NZ	true
156	Nicaragua	NI	true
157	Niger	NE	true
158	Nigeria	NG	true
159	Niue	NU	true
160	Norfolk Island	NF	true
161	Northern Mariana Islands	MP	true
162	Norway	NO	true
163	Oman	OM	true
164	Pakistan	PK	true
165	Palau	PW	true
166	Panama	PA	true
167	Papua New Guinea	PG	true
168	Paraguay	PY	true
169	Peru	PE	true
170	Philippines	PH	true
172	Poland	PL	true
173	Portugal	PT	true
174	Puerto Rico	PR	true
175	Qatar	QA	true
176	Reunion	RE	true
177	Romania	RO	true
178	Russia	RU	true
179	Rwanda	RW	true
180	Saint Lucia	LC	true
181	Samoa	WS	true
182	San Marino	SM	true
183	Sao Tome And Principe	ST	true
184	Saudi Arabia	SA	true
185	Senegal	SN	true
186	Seychelles	SC	true
187	Sierra Leone	SL	true
188	Singapore	SG	true
189	Slovakia	SK	true
190	Slovenia	SI	true
191	Solomon Islands	SB	true
192	Somalia	SO	true
193	South Africa	ZA	true
194	Spain	ES	true
195	Sri Lanka	LK	true
197	Saint Kitts And Nevis	KN	true
199	Saint Vincent And The Grenadines	VC	true
200	Sudan	SD	true
201	Suriname	SR	true
203	Swaziland	SZ	true
204	Sweden	SE	true
205	Switzerland	CH	true
206	Syrian Arab Republic	SY	true
207	Taiwan	TW	true
208	Tajikistan	TJ	true
209	United Republic Of Tanzania	TZ	true
210	Thailand	TH	true
211	Togo	TG	true
212	Tokelau	TK	true
213	Tonga	TO	true
214	Trinidad And Tobago	TT	true
215	Tunisia	TN	true
216	Turkey	TR	true
217	Turkmenistan	TM	true
219	Tuvalu	TV	true
220	Uganda	UG	true
221	Ukraine	UA	true
222	United Arab Emirates	AE	true
223	United Kingdom	GB	true
224	Virgin Islands (U.S.)	VI	true
225	Uruguay	UY	true
226	Uzbekistan	UZ	true
227	Vanuatu	VU	true
228	Holy See (Vatican City State)	VA	true
229	Venezuela	VE	true
230	Vietnam	VN	true
233	Yemen	YE	true
236	Zambia	ZM	true
237	Zimbabwe	ZW	true
238	Palestinian Territory Occupied	PS	true
239	Serbia And Montenegro	CS	true
240	South Georgia And The South Sandwich Islands	GS	true
241	The Democratic Republic Of The Congo	CD	true
242	Timor-Leste	TL	true
243	United States Minor Outlying Islands	UM	true
DATA;

        $countries = collect(preg_split('/\R/', trim($rows)))
            ->filter()
            ->map(function (string $line): array {
                [$id, $name, $code, $status] = array_pad(explode("\t", $line), 4, null);

                return [
                    'id' => (int) $id,
                    'name' => (string) $name,
                    'code' => (string) $code,
                    'status' => strtolower((string) $status) === 'true' ? 1 : 0,
                ];
            })
            ->values()
            ->all();

        DB::table('countries')->upsert(
            $countries,
            ['id'],
            ['name', 'code', 'status']
        );
    }
}
