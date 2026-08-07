<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK Portal - SafeCard v2 (hvkp-safecard2)
 *
 * Ajuste visual del paso de acceso al condominio: los subtitulos
 * "Acceso peatonal" y "Acceso vehicular" quedaban mas chicos que el texto
 * del cuerpo. Ahora tienen jerarquia clara (mas grandes, en negrita y con
 * una linea rosada de separacion).
 *
 * Solo base de datos. Uso:
 *   php hvkp-safecard2.php            (aplicar)
 *   php hvkp-safecard2.php restore    (volver a la version anterior)
 */

$HVKA_BLOCKS = array(
    'es'   => 'PGg0PkFjY2VzbyBhbCBDb25kb21pbmlvIGNvbiBsYSBBcHAgU2FmZUNhcmQ8L2g0Pgo8cCBzdHlsZT0idGV4dC1hbGlnbjpjZW50ZXI7bWFyZ2luOjAgMCA2cHgiPjxpbWcgc3JjPSIvd3AtY29udGVudC91cGxvYWRzL3BvcnRhbC9zYWZlY2FyZC1sb2dvLnBuZyIgYWx0PSJBcHAgU2FmZUNhcmQiIHdpZHRoPSI4NCIgaGVpZ2h0PSI4NCIgc3R5bGU9ImJvcmRlci1yYWRpdXM6MThweDtib3gtc2hhZG93OjAgNnB4IDE4cHggcmdiYSgxNiwyNCw0MCwuMTgpIiAvPjwvcD4KPHAgc3R5bGU9InRleHQtYWxpZ246Y2VudGVyO21hcmdpbjowIDAgMTRweCI+PHN0cm9uZz5TYWZlQ2FyZDwvc3Ryb25nPiAmbWRhc2g7IGJ1c2NhIGVzdGUgJmlhY3V0ZTtjb25vIG5hcmFuam8gZW4gQXBwIFN0b3JlIG8gR29vZ2xlIFBsYXk8L3A+CjxwIHN0eWxlPSJiYWNrZ3JvdW5kOiNmZGYyZjg7Ym9yZGVyLWxlZnQ6NHB4IHNvbGlkICNlODQzOTM7cGFkZGluZzoxMnB4IDE2cHg7Ym9yZGVyLXJhZGl1czo4cHgiPjxzdHJvbmc+RGVzYyZhYWN1dGU7cmdhbGEgYW50ZXMgZGUgbGxlZ2FyLjwvc3Ryb25nPiBMYSBBcHAgY3JlYXImYWFjdXRlOyB0dSB1c3VhcmlvIFJlc2lkZW50ZSBjb24gdHUgbiZ1YWN1dGU7bWVybyBkZSBjZWx1bGFyLiBFbnYmaWFjdXRlO2Fub3MgZXNlIG4mdWFjdXRlO21lcm8gcG9yIFdoYXRzQXBwIHBhcmEgYWN0aXZhcnRlIGVsIGFjY2Vzby48L3A+CjxwIHN0eWxlPSJ0ZXh0LWFsaWduOmNlbnRlcjttYXJnaW46MTRweCAwIj48aW1nIHNyYz0iL3dwLWNvbnRlbnQvdXBsb2Fkcy9wb3J0YWwvc2FmZWNhcmQtYXBwLmpwZyIgYWx0PSJQYW50YWxsYXMgZGUgbGEgQXBwIFNhZmVDYXJkOiBjJm9hY3V0ZTtkaWdvIFFSIGRlIGFjY2VzbyBwZWF0b25hbCB5IGJvdG9uZXMgZGUgZW50cmFkYSB5IHNhbGlkYSB2ZWhpY3VsYXIiIHN0eWxlPSJtYXgtd2lkdGg6MTAwJTtoZWlnaHQ6YXV0bztib3JkZXItcmFkaXVzOjE0cHgiIC8+PC9wPgo8aDUgc3R5bGU9Im1hcmdpbjoyMnB4IDAgOHB4O2ZvbnQtc2l6ZToxLjA2cmVtO2xpbmUtaGVpZ2h0OjEuMztmb250LXdlaWdodDo4MDA7Y29sb3I6IzFjMjAzMztwYWRkaW5nLWJvdHRvbTo4cHg7Ym9yZGVyLWJvdHRvbToycHggc29saWQgI2ZiY2ZlOCI+JiMxMjg2OTQ7IEFjY2VzbyBwZWF0b25hbCAoYSBwaWUpPC9oNT4KPHA+PHN0cm9uZz5MYSBBcHAgZXMgb2JsaWdhdG9yaWEgcGFyYSBlbnRyYXIgWSBwYXJhIHNhbGlyIGEgcGllIGRlbCBjb25kb21pbmlvLjwvc3Ryb25nPiBBYnJlIGxhIHBlc3RhJm50aWxkZTthIDxlbT5QRUFUT05BTDwvZW0+IHkgbXVlc3RyYSBlbCBjJm9hY3V0ZTtkaWdvIFFSIGRlIGxhIEFwcCBlbiBlbCBsZWN0b3IgZGVsIHRvcm5pcXVldGUuIEVsIGMmb2FjdXRlO2RpZ28gY2FtYmlhIGNhZGEgNSBzZWd1bmRvcywgYXMmaWFjdXRlOyBxdWUgZ2VuJmVhY3V0ZTtyYWxvIGp1c3RvIGFsIG1vbWVudG8gZGUgcGFzYXIuIFNpbiBsYSBBcHAgbm8gcG9kciZhYWN1dGU7cyBjcnV6YXIgZWwgYWNjZXNvIHBlYXRvbmFsIGVuIG5pbmd1bm8gZGUgbG9zIGRvcyBzZW50aWRvcy48L3A+CjxoNSBzdHlsZT0ibWFyZ2luOjIycHggMCA4cHg7Zm9udC1zaXplOjEuMDZyZW07bGluZS1oZWlnaHQ6MS4zO2ZvbnQtd2VpZ2h0OjgwMDtjb2xvcjojMWMyMDMzO3BhZGRpbmctYm90dG9tOjhweDtib3JkZXItYm90dG9tOjJweCBzb2xpZCAjZmJjZmU4Ij4mIzEyODY2MzsgQWNjZXNvIHZlaGljdWxhciAoZW4gYXV0byk8L2g1Pgo8cD5UaWVuZXMgZG9zIGZvcm1hcywgZWxpZ2UgbGEgcXVlIHByZWZpZXJhczo8L3A+CjxvbD4KPGxpPjxzdHJvbmc+Q29uIGxhIEFwcDo8L3N0cm9uZz4gYWJyZSBsYSBwZXN0YSZudGlsZGU7YSA8ZW0+VkVISUNVTEFSPC9lbT4geSBwcmVzaW9uYSBlbCBib3Qmb2FjdXRlO24gdmVyZGUgPGVtPkVudHJhZGEgcHJvcGlldGFyaW9zPC9lbT4gKG8gPGVtPlNhbGlkYSBwcm9waWV0YXJpb3M8L2VtPiBhbCBzYWxpcikgcGFyYSBhYnJpciBsYSBiYXJyZXJhLjwvbGk+CjxsaT48c3Ryb25nPkNvbiB0dSBwYXRlbnRlIChtJmFhY3V0ZTtzIHImYWFjdXRlO3BpZG8pOjwvc3Ryb25nPiBlbnYmaWFjdXRlO2Fub3MgbGEgcGF0ZW50ZSBkZSB0dSB2ZWgmaWFjdXRlO2N1bG8gcG9yIFdoYXRzQXBwIHkgbGEgcmVnaXN0cmFtb3MuIERlc2RlIGVzZSBtb21lbnRvIDxzdHJvbmc+bGEgYmFycmVyYSBzZSBhYnJlIHNvbGE8L3N0cm9uZz4gYWwgZGV0ZWN0YXIgdHUgcGF0ZW50ZSwgc2luIHNhY2FyIGVsIGNlbHVsYXIuPC9saT4KPC9vbD4KPHA+SW5ncmVzYSBzaWVtcHJlIHBvciBlbCBhY2Nlc28gcHJpbmNpcGFsIGVuIGxhIDxzdHJvbmc+cGlzdGEgaXpxdWllcmRhPC9zdHJvbmc+LiBQYXJhIHNhbGlyLCB1c2EgZWwgPHN0cm9uZz5jb3N0YWRvIGRlcmVjaG88L3N0cm9uZz46IGxhIGJhcnJlcmEgc2UgYWJyZSBhdXRvbSZhYWN1dGU7dGljYW1lbnRlLCBkZSBhIHVuIGF1dG8gYSBsYSB2ZXouPC9wPg==',
    'en'   => 'PGg0PkNvbmRvbWluaXVtIEFjY2VzcyB3aXRoIHRoZSBTYWZlQ2FyZCBBcHA8L2g0Pgo8cCBzdHlsZT0idGV4dC1hbGlnbjpjZW50ZXI7bWFyZ2luOjAgMCA2cHgiPjxpbWcgc3JjPSIvd3AtY29udGVudC91cGxvYWRzL3BvcnRhbC9zYWZlY2FyZC1sb2dvLnBuZyIgYWx0PSJTYWZlQ2FyZCBBcHAiIHdpZHRoPSI4NCIgaGVpZ2h0PSI4NCIgc3R5bGU9ImJvcmRlci1yYWRpdXM6MThweDtib3gtc2hhZG93OjAgNnB4IDE4cHggcmdiYSgxNiwyNCw0MCwuMTgpIiAvPjwvcD4KPHAgc3R5bGU9InRleHQtYWxpZ246Y2VudGVyO21hcmdpbjowIDAgMTRweCI+PHN0cm9uZz5TYWZlQ2FyZDwvc3Ryb25nPiAmbWRhc2g7IGxvb2sgZm9yIHRoaXMgb3JhbmdlIGljb24gb24gdGhlIEFwcCBTdG9yZSBvciBHb29nbGUgUGxheTwvcD4KPHAgc3R5bGU9ImJhY2tncm91bmQ6I2ZkZjJmODtib3JkZXItbGVmdDo0cHggc29saWQgI2U4NDM5MztwYWRkaW5nOjEycHggMTZweDtib3JkZXItcmFkaXVzOjhweCI+PHN0cm9uZz5Eb3dubG9hZCBpdCBiZWZvcmUgeW91IGFycml2ZS48L3N0cm9uZz4gVGhlIEFwcCBjcmVhdGVzIHlvdXIgUmVzaWRlbnQgYWNjb3VudCB1c2luZyB5b3VyIG1vYmlsZSBudW1iZXIuIFNlbmQgdXMgdGhhdCBudW1iZXIgb24gV2hhdHNBcHAgc28gd2UgY2FuIGFjdGl2YXRlIHlvdXIgYWNjZXNzLjwvcD4KPHAgc3R5bGU9InRleHQtYWxpZ246Y2VudGVyO21hcmdpbjoxNHB4IDAiPjxpbWcgc3JjPSIvd3AtY29udGVudC91cGxvYWRzL3BvcnRhbC9zYWZlY2FyZC1hcHAuanBnIiBhbHQ9IlNhZmVDYXJkIEFwcCBzY3JlZW5zOiBwZWRlc3RyaWFuIFFSIGFjY2VzcyBjb2RlIGFuZCB2ZWhpY2xlIGVudHJ5IGFuZCBleGl0IGJ1dHRvbnMiIHN0eWxlPSJtYXgtd2lkdGg6MTAwJTtoZWlnaHQ6YXV0bztib3JkZXItcmFkaXVzOjE0cHgiIC8+PC9wPgo8aDUgc3R5bGU9Im1hcmdpbjoyMnB4IDAgOHB4O2ZvbnQtc2l6ZToxLjA2cmVtO2xpbmUtaGVpZ2h0OjEuMztmb250LXdlaWdodDo4MDA7Y29sb3I6IzFjMjAzMztwYWRkaW5nLWJvdHRvbTo4cHg7Ym9yZGVyLWJvdHRvbToycHggc29saWQgI2ZiY2ZlOCI+JiMxMjg2OTQ7IFBlZGVzdHJpYW4gYWNjZXNzIChvbiBmb290KTwvaDU+CjxwPjxzdHJvbmc+VGhlIEFwcCBpcyByZXF1aXJlZCBib3RoIHRvIGVudGVyIEFORCB0byBleGl0IHRoZSBjb25kb21pbml1bSBvbiBmb290Ljwvc3Ryb25nPiBPcGVuIHRoZSA8ZW0+UEVBVE9OQUw8L2VtPiB0YWIgYW5kIHNob3cgdGhlIEFwcCZyc3F1bztzIFFSIGNvZGUgdG8gdGhlIHR1cm5zdGlsZSByZWFkZXIuIFRoZSBjb2RlIGNoYW5nZXMgZXZlcnkgNSBzZWNvbmRzLCBzbyBnZW5lcmF0ZSBpdCByaWdodCBhcyB5b3Ugd2FsayB0aHJvdWdoLiBXaXRob3V0IHRoZSBBcHAgeW91IGNhbm5vdCBwYXNzIHRoZSBwZWRlc3RyaWFuIGdhdGUgaW4gZWl0aGVyIGRpcmVjdGlvbi48L3A+CjxoNSBzdHlsZT0ibWFyZ2luOjIycHggMCA4cHg7Zm9udC1zaXplOjEuMDZyZW07bGluZS1oZWlnaHQ6MS4zO2ZvbnQtd2VpZ2h0OjgwMDtjb2xvcjojMWMyMDMzO3BhZGRpbmctYm90dG9tOjhweDtib3JkZXItYm90dG9tOjJweCBzb2xpZCAjZmJjZmU4Ij4mIzEyODY2MzsgVmVoaWNsZSBhY2Nlc3MgKGJ5IGNhcik8L2g1Pgo8cD5Zb3UgaGF2ZSB0d28gb3B0aW9ucywgd2hpY2hldmVyIHlvdSBwcmVmZXI6PC9wPgo8b2w+CjxsaT48c3Ryb25nPldpdGggdGhlIEFwcDo8L3N0cm9uZz4gb3BlbiB0aGUgPGVtPlZFSElDVUxBUjwvZW0+IHRhYiBhbmQgcHJlc3MgdGhlIGdyZWVuIDxlbT5FbnRyYWRhIHByb3BpZXRhcmlvczwvZW0+IGJ1dHRvbiAob3IgPGVtPlNhbGlkYSBwcm9waWV0YXJpb3M8L2VtPiB3aGVuIGxlYXZpbmcpIHRvIHJhaXNlIHRoZSBiYXJyaWVyLjwvbGk+CjxsaT48c3Ryb25nPldpdGggeW91ciBsaWNlbnNlIHBsYXRlIChmYXN0ZXIpOjwvc3Ryb25nPiBzZW5kIHVzIHlvdXIgcGxhdGUgbnVtYmVyIG9uIFdoYXRzQXBwIGFuZCB3ZSB3aWxsIHJlZ2lzdGVyIGl0LiBGcm9tIHRoZW4gb24gPHN0cm9uZz50aGUgYmFycmllciBvcGVucyBieSBpdHNlbGY8L3N0cm9uZz4gd2hlbiBpdCByZWFkcyB5b3VyIHBsYXRlICZtZGFzaDsgbm8gcGhvbmUgbmVlZGVkLjwvbGk+Cjwvb2w+CjxwPkFsd2F5cyBlbnRlciB0aHJvdWdoIHRoZSBtYWluIGdhdGUgb24gdGhlIDxzdHJvbmc+bGVmdCBsYW5lPC9zdHJvbmc+LiBUbyBleGl0LCB1c2UgdGhlIDxzdHJvbmc+cmlnaHQtaGFuZCBzaWRlPC9zdHJvbmc+OiB0aGUgYmFycmllciBvcGVucyBhdXRvbWF0aWNhbGx5LCBvbmUgY2FyIGF0IGEgdGltZS48L3A+',
    'ptbr' => 'PGg0PkFjZXNzbyBhbyBDb25kb21pbmlvIGNvbSBvIEFwcCBTYWZlQ2FyZDwvaDQ+CjxwIHN0eWxlPSJ0ZXh0LWFsaWduOmNlbnRlcjttYXJnaW46MCAwIDZweCI+PGltZyBzcmM9Ii93cC1jb250ZW50L3VwbG9hZHMvcG9ydGFsL3NhZmVjYXJkLWxvZ28ucG5nIiBhbHQ9IkFwcCBTYWZlQ2FyZCIgd2lkdGg9Ijg0IiBoZWlnaHQ9Ijg0IiBzdHlsZT0iYm9yZGVyLXJhZGl1czoxOHB4O2JveC1zaGFkb3c6MCA2cHggMThweCByZ2JhKDE2LDI0LDQwLC4xOCkiIC8+PC9wPgo8cCBzdHlsZT0idGV4dC1hbGlnbjpjZW50ZXI7bWFyZ2luOjAgMCAxNHB4Ij48c3Ryb25nPlNhZmVDYXJkPC9zdHJvbmc+ICZtZGFzaDsgcHJvY3VyZSBlc3RlICZpYWN1dGU7Y29uZSBsYXJhbmphIG5hIEFwcCBTdG9yZSBvdSBHb29nbGUgUGxheTwvcD4KPHAgc3R5bGU9ImJhY2tncm91bmQ6I2ZkZjJmODtib3JkZXItbGVmdDo0cHggc29saWQgI2U4NDM5MztwYWRkaW5nOjEycHggMTZweDtib3JkZXItcmFkaXVzOjhweCI+PHN0cm9uZz5CYWl4ZSBhbnRlcyBkZSBjaGVnYXIuPC9zdHJvbmc+IE8gQXBwIGNyaWEgc2V1IHVzdSZhYWN1dGU7cmlvIFJlc2lkZW50ZSBjb20gc2V1IG4mdWFjdXRlO21lcm8gZGUgY2VsdWxhci4gRW52aWUgZXNzZSBuJnVhY3V0ZTttZXJvIHBlbG8gV2hhdHNBcHAgcGFyYSBhdGl2YXJtb3Mgc2V1IGFjZXNzby48L3A+CjxwIHN0eWxlPSJ0ZXh0LWFsaWduOmNlbnRlcjttYXJnaW46MTRweCAwIj48aW1nIHNyYz0iL3dwLWNvbnRlbnQvdXBsb2Fkcy9wb3J0YWwvc2FmZWNhcmQtYXBwLmpwZyIgYWx0PSJUZWxhcyBkbyBBcHAgU2FmZUNhcmQ6IGNvZGlnbyBRUiBkZSBhY2Vzc28gcGVkZXN0cmUgZSBib3RvZXMgZGUgZW50cmFkYSBlIHNhaWRhIHZlaWN1bGFyIiBzdHlsZT0ibWF4LXdpZHRoOjEwMCU7aGVpZ2h0OmF1dG87Ym9yZGVyLXJhZGl1czoxNHB4IiAvPjwvcD4KPGg1IHN0eWxlPSJtYXJnaW46MjJweCAwIDhweDtmb250LXNpemU6MS4wNnJlbTtsaW5lLWhlaWdodDoxLjM7Zm9udC13ZWlnaHQ6ODAwO2NvbG9yOiMxYzIwMzM7cGFkZGluZy1ib3R0b206OHB4O2JvcmRlci1ib3R0b206MnB4IHNvbGlkICNmYmNmZTgiPiYjMTI4Njk0OyBBY2Vzc28gZGUgcGVkZXN0cmVzIChhIHAmZWFjdXRlOyk8L2g1Pgo8cD48c3Ryb25nPk8gQXBwICZlYWN1dGU7IG9icmlnYXQmb2FjdXRlO3JpbyB0YW50byBwYXJhIGVudHJhciBxdWFudG8gcGFyYSBzYWlyIGEgcCZlYWN1dGU7IGRvIGNvbmRvbSZpYWN1dGU7bmlvLjwvc3Ryb25nPiBBYnJhIGEgYWJhIDxlbT5QRUFUT05BTDwvZW0+IGUgbW9zdHJlIG8gYyZvYWN1dGU7ZGlnbyBRUiBkbyBBcHAgbm8gbGVpdG9yIGRhIGNhdHJhY2EuIE8gYyZvYWN1dGU7ZGlnbyBtdWRhIGEgY2FkYSA1IHNlZ3VuZG9zLCBlbnQmYXRpbGRlO28gZ2VyZS1vIG5hIGhvcmEgZGUgcGFzc2FyLiBTZW0gbyBBcHAgdm9jJmVjaXJjOyBuJmF0aWxkZTtvIGNvbnNlZ3VlIGNydXphciBvIGFjZXNzbyBkZSBwZWRlc3RyZXMgZW0gbmVuaHVtYSBkYXMgZGlyZSZjY2VkaWw7Jm90aWxkZTtlcy48L3A+CjxoNSBzdHlsZT0ibWFyZ2luOjIycHggMCA4cHg7Zm9udC1zaXplOjEuMDZyZW07bGluZS1oZWlnaHQ6MS4zO2ZvbnQtd2VpZ2h0OjgwMDtjb2xvcjojMWMyMDMzO3BhZGRpbmctYm90dG9tOjhweDtib3JkZXItYm90dG9tOjJweCBzb2xpZCAjZmJjZmU4Ij4mIzEyODY2MzsgQWNlc3NvIHZlaWN1bGFyIChkZSBjYXJybyk8L2g1Pgo8cD5Wb2MmZWNpcmM7IHRlbSBkdWFzIG9wJmNjZWRpbDsmb3RpbGRlO2VzLCBlc2NvbGhhIGEgcXVlIHByZWZlcmlyOjwvcD4KPG9sPgo8bGk+PHN0cm9uZz5Db20gbyBBcHA6PC9zdHJvbmc+IGFicmEgYSBhYmEgPGVtPlZFSElDVUxBUjwvZW0+IGUgcHJlc3Npb25lIG8gYm90JmF0aWxkZTtvIHZlcmRlIDxlbT5FbnRyYWRhIHByb3BpZXRhcmlvczwvZW0+IChvdSA8ZW0+U2FsaWRhIHByb3BpZXRhcmlvczwvZW0+IGFvIHNhaXIpIHBhcmEgYWJyaXIgYSBjYW5jZWxhLjwvbGk+CjxsaT48c3Ryb25nPkNvbSBzdWEgcGxhY2EgKG1haXMgciZhYWN1dGU7cGlkbyk6PC9zdHJvbmc+IGVudmllIGEgcGxhY2EgZG8gc2V1IHZlJmlhY3V0ZTtjdWxvIHBlbG8gV2hhdHNBcHAgZSBuJm9hY3V0ZTtzIGEgY2FkYXN0cmFtb3MuIEEgcGFydGlyIGRhJmlhY3V0ZTsgPHN0cm9uZz5hIGNhbmNlbGEgYWJyZSBzb3ppbmhhPC9zdHJvbmc+IGFvIGxlciBzdWEgcGxhY2EsIHNlbSBwcmVjaXNhciBkbyBjZWx1bGFyLjwvbGk+Cjwvb2w+CjxwPkVudHJlIHNlbXByZSBwZWxvIGFjZXNzbyBwcmluY2lwYWwgbmEgPHN0cm9uZz5waXN0YSBlc3F1ZXJkYTwvc3Ryb25nPi4gUGFyYSBzYWlyLCB1c2UgbyA8c3Ryb25nPmxhZG8gZGlyZWl0bzwvc3Ryb25nPjogYSBjYW5jZWxhIGFicmUgYXV0b21hdGljYW1lbnRlLCB1bSBjYXJybyBwb3IgdmV6LjwvcD4=',
);
$HVKA_MARCA = 'border-bottom:2px solid #fbcfe8';

function hvka_sc2_replace( $html, $nuevo, $marca ) {
    if ( ! is_string( $html ) || '' === trim( $html ) ) {
        return array( null, 'contenido-vacio' );
    }
    if ( false !== strpos( $html, $marca ) ) {
        return array( null, 'ya-instalado' );
    }
    $partes = preg_split( '/(?=<h4>)/i', $html );
    $salida = '';
    $puesto = false;
    $n      = 0;
    foreach ( $partes as $parte ) {
        if ( '' === trim( $parte ) ) {
            continue;
        }
        $es_h4 = ( 0 === stripos( ltrim( $parte ), '<h4>' ) );
        if ( $es_h4 && preg_match( '/safecard/i', $parte ) ) {
            $n++;
            if ( ! $puesto ) {
                $salida .= $nuevo;
                $puesto  = true;
            }
            continue;
        }
        $salida .= $parte;
    }
    if ( ! $puesto ) {
        return array( null, 'no-se-encontro-el-paso-safecard' );
    }
    return array( $salida, 'ok:' . $n );
}

if ( getenv( 'HVKASC2_TEST' ) ) {
    $fail = 0;
    $chk  = function ( $l, $c ) use ( &$fail ) { echo ( $c ? "[OK]    " : "[ERROR] " ) . $l . "\n"; if ( ! $c ) { $fail++; } };
    $nuevo = base64_decode( $GLOBALS['HVKA_BLOCKS']['es'] );
    $prev  = '<h3>Check-in</h3><p>intro</p>'
        . '<h4>Paso 1: Check-in en Conserjeria</h4><p>conserje</p>'
        . '<h4>Acceso al Condominio con la App SafeCard</h4><p>version anterior con safecard-app.jpg</p>'
        . '<h4>Ingreso al Edificio y Estacionamientos</h4><p>N&deg; 19</p>'
        . '<h4>Ingreso al Departamento</h4><p>1960</p>';
    list( $out, $why ) = hvka_sc2_replace( $prev, $nuevo, $GLOBALS['HVKA_MARCA'] );
    $chk( 'reemplaza el paso SafeCard existente', 'ok:1' === $why );
    $chk( 'siguen siendo 4 pasos', 4 === substr_count( strtolower( $out ), '<h4>' ) );
    $chk( 'conserva conserjeria, estacionamiento y codigo', strpos( $out, 'Conserjeria' ) !== false && strpos( $out, 'N&deg; 19' ) !== false && strpos( $out, '1960' ) !== false );
    $chk( 'subtitulos con jerarquia nueva', 2 === substr_count( $out, $GLOBALS['HVKA_MARCA'] ) );
    $chk( 'conserva imagenes y textos clave', strpos( $out, 'safecard-app.jpg' ) !== false && stripos( $out, 'entrar Y para salir' ) !== false && stripos( $out, 'abre sola' ) !== false );
    list( $o2, $w2 ) = hvka_sc2_replace( $out, $nuevo, $GLOBALS['HVKA_MARCA'] );
    $chk( 'idempotente', null === $o2 && 'ya-instalado' === $w2 );
    list( $o3, $w3 ) = hvka_sc2_replace( '<h4>Otro paso</h4><p>sin menciones</p>', $nuevo, $GLOBALS['HVKA_MARCA'] );
    $chk( 'sin paso SafeCard: no toca nada', null === $o3 );
    foreach ( array( 'es', 'en', 'ptbr' ) as $l ) {
        $b = base64_decode( $GLOBALS['HVKA_BLOCKS'][ $l ] );
        $chk( "bloque $l con 2 subtitulos nuevos", 2 === substr_count( $b, $GLOBALS['HVKA_MARCA'] ) );
    }
    echo $fail ? "FALLARON $fail pruebas\n" : "TODAS LAS PRUEBAS PASARON\n";
    exit( $fail ? 1 : 0 );
}

require __DIR__ . '/wp-load.php';
global $wpdb;

$mode  = isset( $argv[1] ) ? $argv[1] : 'install';
$tabla = $wpdb->prefix . 'portal_units';
$ok    = array();
$warn  = array();

if ( 'restore' === $mode ) {
    $b = get_option( 'hvkp_safecard2_backup' );
    if ( ! is_array( $b ) || empty( $b ) ) {
        echo "[AVISO] No hay respaldo; nada que restaurar.\n";
        exit( 0 );
    }
    foreach ( $b as $fila ) {
        $wpdb->update( $tabla, array( $fila['campo'] => $fila['valor'] ), array( 'id' => (int) $fila['id'] ) );
    }
    echo "[OK]    Contenido restaurado (" . count( $b ) . " campos)\n";
    exit( 0 );
}

$unidades = $wpdb->get_results( "SELECT id, name FROM $tabla WHERE active = 1" );
if ( ! $unidades ) {
    echo "[ERROR] No se encontraron unidades activas.\n";
    exit( 1 );
}
$backup = array();
foreach ( $unidades as $u ) {
    foreach ( $HVKA_BLOCKS as $lang => $b64 ) {
        $campo  = 'checkin_' . $lang;
        $actual = $wpdb->get_var( $wpdb->prepare( "SELECT `$campo` FROM $tabla WHERE id = %d", $u->id ) );
        list( $nuevo, $motivo ) = hvka_sc2_replace( (string) $actual, base64_decode( $b64 ), $HVKA_MARCA );
        if ( 'ya-instalado' === $motivo ) {
            $ok[] = "$u->name [$lang]: ya estaba ajustado";
            continue;
        }
        if ( null === $nuevo ) {
            $warn[] = "$u->name [$lang]: sin cambios ($motivo)";
            continue;
        }
        $backup[] = array( 'id' => (int) $u->id, 'campo' => $campo, 'valor' => (string) $actual );
        $wpdb->update( $tabla, array( $campo => $nuevo ), array( 'id' => (int) $u->id ) );
        $ok[] = "$u->name [$lang]: subtitulos de acceso con jerarquia clara";
    }
}
if ( $backup ) {
    update_option( 'hvkp_safecard2_backup', $backup, false );
}

if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
do_action( 'litespeed_purge_all' );
if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); }
if ( function_exists( 'w3tc_flush_all' ) ) { w3tc_flush_all(); }
$ok[] = 'Caches purgadas';

echo "== HOMVUK Portal - SafeCard v2 (ajuste visual) ==\n";
foreach ( $ok as $m )   { echo "[OK]    $m\n"; }
foreach ( $warn as $m ) { echo "[AVISO] $m\n"; }
echo "Listo. Este instalador puede borrarse: rm -f hvkp-safecard2.php\n";
