<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $rows = <<<'DATA'
1	1	New York	NY	true
2	1	Virgin Islands	VI	true
3	1	Massachusetts	MA	true
4	1	Rhode Island	RI	true
5	1	New Hampshire	NH	true
6	1	Maine	ME	true
7	1	Vermont	VT	true
8	1	Connecticut	CT	true
9	1	New Jersey	NJ	true
10	1	Pennsylvania	PA	true
11	1	Delaware	DE	true
12	1	District Of Columbia	DC	true
13	1	Maryland	MD	true
14	1	West Virginia	WV	true
15	1	Texas	TX	true
16	1	South Carolina	SC	true
17	1	Georgia	GA	true
18	1	Florida	FL	true
19	1	North Carolina	NC	true
20	1	Tennessee	TN	true
21	1	Mississippi	MS	true
22	1	Kentucky	KY	true
23	1	Ohio	OH	true
24	1	Indiana	IN	true
25	1	Michigan	MI	true
26	1	Iowa	IA	true
27	1	Wisconsin	WI	true
28	1	Minnesota	MN	true
29	1	South Dakota	SD	true
30	1	North Dakota	ND	true
31	1	Montana	MT	true
32	1	Illinois	IL	true
33	1	Missouri	MO	true
34	1	Kansas	KS	true
35	1	Nebraska	NE	true
36	1	Louisiana	LA	true
37	1	Arkansas	AR	true
38	1	Oklahoma	OK	true
39	1	Colorado	CO	true
40	1	Wyoming	WY	true
41	1	Idaho	ID	true
42	1	Utah	UT	true
43	1	Arizona	AZ	true
44	1	New Mexico	NM	true
45	1	Nevada	NV	true
46	1	California	CA	true
47	1	Hawaii	HI	true
48	1	Oregon	OR	true
49	1	Washington	WA	true
50	1	Alaska	AK	true
51	1	Alabama	AL	true
52	1	Virginia	VA	true
53	101	Maharastra	MH	true
54	101	West Bengal	WB	true
55	101	Gujarat	GJ	true
56	162	North		true
57	101	New Delhi		true
64	101	Rajasthan		true
65	101	Punjab		true
67	101	Karnataka		true
68	101	Madhya Pradesh		true
69	2	Kabul		true
70	101	Kerala		true
71	101	Himachal Pradesh		true
72	101	Uttar Pradesh		true
73	101	Tamil Nadu		true
74	101	Orissa		true
75	101	Goa		true
77	101	Bihar		true
78	101	Haryana		true
79	101	Jharkhand		true
80	101	Manipur		true
81	101	Meghalaya		true
82	101	Maharashtra		true
83	101	Arunachal Pradesh		true
86	101	Gujarat	GJ	true
87	101	Assam		true
88	223	Brent		true
89	223	Bexley		true
90	223	Belfast		true
91	223	Bridgend		true
92	223	Blaenau Gwent		true
93	223	Birmingham		true
94	223	Buckinghamshire		true
95	223	Ballymena		true
96	223	Ballymoney		true
97	223	Bournemouth		true
98	223	Banbridge		true
99	223	Barnet		true
100	223	Brighton And Hove		true
101	223	Barnsley		true
102	223	Bolton		true
103	223	Blackpool		true
104	223	Bracknell Forest		true
105	223	Bradford		true
106	223	Bromley		true
107	223	Bristol		true
108	223	Bury		true
109	223	Cambridgeshire		true
110	223	Caerphilly		true
DATA;

        $states = collect(preg_split('/\R/', trim($rows)))
            ->filter()
            ->map(function (string $line): array {
                [$id, $countryId, $name, $code, $status] = array_pad(explode("\t", $line), 5, null);

                return [
                    'id' => (int) $id,
                    'country_id' => (int) $countryId,
                    'name' => (string) $name,
                    'code' => ($code === null || $code === '') ? null : (string) $code,
                    'status' => strtolower((string) $status) === 'true' ? 1 : 0,
                ];
            })
            ->values()
            ->all();

        DB::table('states')->upsert(
            $states,
            ['id'],
            ['country_id', 'name', 'code', 'status']
        );
    }
}
