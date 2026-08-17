<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK Portal - Seccion Electrodomesticos (hvkp-electro)
 *
 *   1. Descarga el manual del lavavajillas Teka y lo deja publicado en
 *      uploads/portal/manual-lavavajillas-teka.pdf (verificado por md5).
 *   2. Crea el bloque "Electrodomesticos" en Informacion General con las
 *      instrucciones simples del lavavajillas Teka LP8 850 y el enlace al
 *      manual completo.
 *   3. Si la unidad tiene un bloque suelto de secadora, lo mueve DENTRO de
 *      Electrodomesticos (conservando su texto y su video) y elimina el
 *      bloque suelto, para que los electrodomesticos queden juntos.
 *
 * Uso:  php hvkp-electro.php            (aplicar)
 *       php hvkp-electro.php restore    (revertir)
 */

$HVKE_BLOQUES = array( 'es' => 'PGgzPkVsZWN0cm9kb20mZWFjdXRlO3N0aWNvczwvaDM+CjxoND5MYXZhdmFqaWxsYXMgVGVrYTwvaDQ+CjxwPkVsIGRlcGFydGFtZW50byB0aWVuZSBsYXZhdmFqaWxsYXMgPHN0cm9uZz5UZWthIExQOCA4NTA8L3N0cm9uZz4gKGNhcGFjaWRhZCAxNCBzZXJ2aWNpb3MpLiBVc2FybG8gZXMgbXV5IHNpbXBsZTo8L3A+CjxvbD4KPGxpPjxzdHJvbmc+Q2FyZ2EgbG9zIHBsYXRvcy48L3N0cm9uZz4gUHJpbWVybyBsYSBiYW5kZWphIGRlIGFiYWpvIChvbGxhcywgcGxhdG9zIGdyYW5kZXMsIGZ1ZW50ZXMpIHkgZGVzcHUmZWFjdXRlO3MgbGEgZGUgYXJyaWJhICh2YXNvcywgdGF6YXMsIGNvcGFzKS4gTGFzIHBpZXphcyBob25kYXMgdmFuIDxlbT5ib2NhIGFiYWpvPC9lbT4geSBsb3MgY3VjaGlsbG9zIGxhcmdvcywgYWNvc3RhZG9zIGVuIGxhIGJhbmRlamEgc3VwZXJpb3IuPC9saT4KPGxpPjxzdHJvbmc+UG9uIGVsIGRldGVyZ2VudGU8L3N0cm9uZz4gKHBhc3RpbGxhIG8gcG9sdm8pIGVuIGVsIGRpc3BlbnNhZG9yIHF1ZSBlc3QmYWFjdXRlOyBlbiBsYSBjYXJhIGludGVyaW9yIGRlIGxhIHB1ZXJ0YSwgeSBjaSZlYWN1dGU7cnJhbG8uPC9saT4KPGxpPjxzdHJvbmc+QWJyZSBsYSBsbGF2ZSBkZWwgYWd1YTwvc3Ryb25nPiB5IHByZXNpb25hIGVsIGJvdCZvYWN1dGU7biBkZSBlbmNlbmRpZG8uPC9saT4KPGxpPjxzdHJvbmc+RWxpZ2UgZWwgcHJvZ3JhbWE8L3N0cm9uZz4gY29uIGVsIGJvdCZvYWN1dGU7biBkZSBwcm9ncmFtYXMuPC9saT4KPGxpPjxzdHJvbmc+Q2llcnJhIGxhIHB1ZXJ0YS48L3N0cm9uZz4gRWwgbGF2YXZhamlsbGFzIHBhcnRlIHNvbG8gdW5vcyAxMCBzZWd1bmRvcyBkZXNwdSZlYWN1dGU7cy48L2xpPgo8L29sPgo8cD48c3Ryb25nPiZpcXVlc3Q7UXUmZWFjdXRlOyBwcm9ncmFtYSBlbGlqbz88L3N0cm9uZz48L3A+Cjx1bD4KPGxpPjxzdHJvbmc+RUNPPC9zdHJvbmc+ICZtZGFzaDsgZWwgZGUgc2llbXByZSBwYXJhIGVsIHVzbyBkaWFyaW86IGdhc3RhIG1lbm9zIGFndWEgeSBsdXouPC9saT4KPGxpPjxzdHJvbmc+UiZhYWN1dGU7cGlkbzwvc3Ryb25nPiAoNDAgbWluKSAmbWRhc2g7IHBvY2EgbG96YSB5IHBvY28gc3VjaWEsIGN1YW5kbyB0aWVuZXMgYXB1cm8uPC9saT4KPGxpPjxzdHJvbmc+SW50ZW5zaXZvPC9zdHJvbmc+ICZtZGFzaDsgb2xsYXMgeSBmdWVudGVzIGNvbiBjb21pZGEgcGVnYWRhLjwvbGk+CjxsaT48c3Ryb25nPjEgSG9yYTwvc3Ryb25nPiBvIDxzdHJvbmc+Q3Jpc3RhbDwvc3Ryb25nPiAmbWRhc2g7IGNhcmdhIGxpdmlhbmE7IENyaXN0YWwgZXMgbSZhYWN1dGU7cyBzdWF2ZSBwYXJhIGNvcGFzLjwvbGk+CjwvdWw+CjxwPkFsIHRlcm1pbmFyIHN1ZW5hIHVuIGF2aXNvLiBBcGFnYSBlbCBlcXVpcG8sIDxzdHJvbmc+ZXNwZXJhIHVub3MgMTUgbWludXRvcyBhbnRlcyBkZSBzYWNhciBsYSBsb3phPC9zdHJvbmc+OiBzYWxlIG11eSBjYWxpZW50ZSB5IGFzJmlhY3V0ZTsgdGVybWluYSBkZSBzZWNhcnNlLiBWYWMmaWFjdXRlO2EgcHJpbWVybyBsYSBiYW5kZWphIGRlIGFiYWpvIHBhcmEgcXVlIG5vIGxlIGdvdGVlIGxhIGRlIGFycmliYS48L3A+CjxwPkVzIG5vcm1hbCBxdWUgcXVlZGUgYWxnbyBkZSBodW1lZGFkIGVuIGVsIGludGVyaW9yLiBTaSB1c2FzIHBhc3RpbGxhcywgcHJlZmllcmUgcHJvZ3JhbWFzIGxhcmdvczogZW4gZWwgcHJvZ3JhbWEgUiZhYWN1dGU7cGlkbyBubyBhbGNhbnphbiBhIGRpc29sdmVyc2UgcG9yIGNvbXBsZXRvLjwvcD4KPHAgc3R5bGU9ImJhY2tncm91bmQ6I2Y2ZjdmYjtib3JkZXI6MXB4IHNvbGlkICNlNmU4ZWY7Ym9yZGVyLWxlZnQ6NHB4IHNvbGlkICNlODQzOTM7Ym9yZGVyLXJhZGl1czoxMnB4O3BhZGRpbmc6MTRweCAxNnB4O21hcmdpbjoxMnB4IDAiPjxzdHJvbmc+JiMxMjgxOTY7IE1hbnVhbCBjb21wbGV0byBkZWwgbGF2YXZhamlsbGFzPC9zdHJvbmc+PGJyPjxhIGhyZWY9Il9fUERGX1VSTF9fIiB0YXJnZXQ9Il9ibGFuayIgcmVsPSJub29wZW5lciI+RGVzY2FyZ2FyIG1hbnVhbCBUZWthIExQOCA4NTAgKFBERik8L2E+PGJyPjxzcGFuIHN0eWxlPSJmb250LXNpemU6Ljg1cmVtO2NvbG9yOiM2YTcxODYiPkluY2x1eWUgbGEgdGFibGEgY29tcGxldGEgZGUgcHJvZ3JhbWFzLCBsaW1waWV6YSBkZSBmaWx0cm9zIHkgYyZvYWN1dGU7ZGlnb3MgZGUgZXJyb3IuPC9zcGFuPjwvcD4K', 'en' => 'PGgzPkFwcGxpYW5jZXM8L2gzPgo8aDQ+VGVrYSBEaXNod2FzaGVyPC9oND4KPHA+VGhlIGFwYXJ0bWVudCBoYXMgYSA8c3Ryb25nPlRla2EgTFA4IDg1MDwvc3Ryb25nPiBkaXNod2FzaGVyICgxNCBwbGFjZSBzZXR0aW5ncykuIFVzaW5nIGl0IGlzIHZlcnkgc2ltcGxlOjwvcD4KPG9sPgo8bGk+PHN0cm9uZz5Mb2FkIHRoZSBkaXNoZXMuPC9zdHJvbmc+IEJvdHRvbSBiYXNrZXQgZmlyc3QgKHBvdHMsIGxhcmdlIHBsYXRlcywgc2VydmluZyBkaXNoZXMpLCB0aGVuIHRoZSB0b3Agb25lIChnbGFzc2VzLCBjdXBzKS4gSG9sbG93IGl0ZW1zIGdvIDxlbT5mYWNpbmcgZG93bjwvZW0+LCBhbmQgbG9uZyBrbml2ZXMgbGllIGZsYXQgaW4gdGhlIHVwcGVyIGJhc2tldC48L2xpPgo8bGk+PHN0cm9uZz5BZGQgdGhlIGRldGVyZ2VudDwvc3Ryb25nPiAodGFibGV0IG9yIHBvd2RlcikgdG8gdGhlIGRpc3BlbnNlciBvbiB0aGUgaW5zaWRlIG9mIHRoZSBkb29yIGFuZCBjbG9zZSBpdC48L2xpPgo8bGk+PHN0cm9uZz5PcGVuIHRoZSB3YXRlciB0YXA8L3N0cm9uZz4gYW5kIHByZXNzIHRoZSBwb3dlciBidXR0b24uPC9saT4KPGxpPjxzdHJvbmc+Q2hvb3NlIHRoZSBwcm9ncmFtbWU8L3N0cm9uZz4gd2l0aCB0aGUgcHJvZ3JhbW1lIGJ1dHRvbi48L2xpPgo8bGk+PHN0cm9uZz5DbG9zZSB0aGUgZG9vci48L3N0cm9uZz4gVGhlIGRpc2h3YXNoZXIgc3RhcnRzIG9uIGl0cyBvd24gYWZ0ZXIgYWJvdXQgMTAgc2Vjb25kcy48L2xpPgo8L29sPgo8cD48c3Ryb25nPldoaWNoIHByb2dyYW1tZSBzaG91bGQgSSB1c2U/PC9zdHJvbmc+PC9wPgo8dWw+CjxsaT48c3Ryb25nPkVDTzwvc3Ryb25nPiAmbWRhc2g7IHRoZSBldmVyeWRheSBvcHRpb246IHVzZXMgdGhlIGxlYXN0IHdhdGVyIGFuZCBlbmVyZ3kuPC9saT4KPGxpPjxzdHJvbmc+UmFwaWQ8L3N0cm9uZz4gKDQwIG1pbikgJm1kYXNoOyBzbWFsbCwgbGlnaHRseSBzb2lsZWQgbG9hZHMgd2hlbiB5b3UgYXJlIGluIGEgaHVycnkuPC9saT4KPGxpPjxzdHJvbmc+SW50ZW5zaXZlPC9zdHJvbmc+ICZtZGFzaDsgcG90cyBhbmQgcGFucyB3aXRoIGRyaWVkLW9uIGZvb2QuPC9saT4KPGxpPjxzdHJvbmc+MSBIb3VyPC9zdHJvbmc+IG9yIDxzdHJvbmc+R2xhc3M8L3N0cm9uZz4gJm1kYXNoOyBsaWdodCBsb2FkczsgR2xhc3MgaXMgZ2VudGxlciBvbiBzdGVtd2FyZS48L2xpPgo8L3VsPgo8cD5BIGJlZXAgc291bmRzIHdoZW4gdGhlIGN5Y2xlIGVuZHMuIFN3aXRjaCB0aGUgYXBwbGlhbmNlIG9mZiBhbmQgPHN0cm9uZz53YWl0IGFib3V0IDE1IG1pbnV0ZXMgYmVmb3JlIHVubG9hZGluZzwvc3Ryb25nPjogdGhlIGRpc2hlcyBjb21lIG91dCB2ZXJ5IGhvdCBhbmQgZmluaXNoIGRyeWluZyBpbiB0aGF0IHRpbWUuIEVtcHR5IHRoZSBib3R0b20gYmFza2V0IGZpcnN0IHNvIHRoZSB0b3Agb25lIGRvZXMgbm90IGRyaXAgb24gaXQuPC9wPgo8cD5Tb21lIG1vaXN0dXJlIGluc2lkZSBpcyBub3JtYWwuIElmIHlvdSB1c2UgdGFibGV0cywgcHJlZmVyIHRoZSBsb25nZXIgcHJvZ3JhbW1lczogdGhleSBkbyBub3QgZnVsbHkgZGlzc29sdmUgZHVyaW5nIHRoZSBSYXBpZCBjeWNsZS48L3A+CjxwIHN0eWxlPSJiYWNrZ3JvdW5kOiNmNmY3ZmI7Ym9yZGVyOjFweCBzb2xpZCAjZTZlOGVmO2JvcmRlci1sZWZ0OjRweCBzb2xpZCAjZTg0MzkzO2JvcmRlci1yYWRpdXM6MTJweDtwYWRkaW5nOjE0cHggMTZweDttYXJnaW46MTJweCAwIj48c3Ryb25nPiYjMTI4MTk2OyBGdWxsIGRpc2h3YXNoZXIgbWFudWFsPC9zdHJvbmc+PGJyPjxhIGhyZWY9Il9fUERGX1VSTF9fIiB0YXJnZXQ9Il9ibGFuayIgcmVsPSJub29wZW5lciI+RG93bmxvYWQgdGhlIFRla2EgTFA4IDg1MCBtYW51YWwgKFBERik8L2E+PGJyPjxzcGFuIHN0eWxlPSJmb250LXNpemU6Ljg1cmVtO2NvbG9yOiM2YTcxODYiPkluY2x1ZGVzIHRoZSBmdWxsIHByb2dyYW1tZSB0YWJsZSwgZmlsdGVyIGNsZWFuaW5nIGFuZCBlcnJvciBjb2Rlcy48L3NwYW4+PC9wPgo=', 'ptbr' => 'PGgzPkVsZXRyb2RvbSZlYWN1dGU7c3RpY29zPC9oMz4KPGg0PkxhdmEtbG91JmNjZWRpbDthcyBUZWthPC9oND4KPHA+TyBhcGFydGFtZW50byB0ZW0gbGF2YS1sb3UmY2NlZGlsO2FzIDxzdHJvbmc+VGVrYSBMUDggODUwPC9zdHJvbmc+ICgxNCBzZXJ2aSZjY2VkaWw7b3MpLiBVc2FyICZlYWN1dGU7IGJlbSBzaW1wbGVzOjwvcD4KPG9sPgo8bGk+PHN0cm9uZz5Db2xvcXVlIGEgbG91JmNjZWRpbDthLjwvc3Ryb25nPiBQcmltZWlybyBvIGNlc3RvIGRlIGJhaXhvIChwYW5lbGFzLCBwcmF0b3MgZ3JhbmRlcywgdHJhdmVzc2FzKSBlIGRlcG9pcyBvIGRlIGNpbWEgKGNvcG9zLCB4JmlhY3V0ZTtjYXJhcywgdGEmY2NlZGlsO2FzKS4gQXMgcGUmY2NlZGlsO2FzIGZ1bmRhcyB2JmF0aWxkZTtvIDxlbT52aXJhZGFzIHBhcmEgYmFpeG88L2VtPiBlIGFzIGZhY2FzIGxvbmdhcywgZGVpdGFkYXMgbm8gY2VzdG8gc3VwZXJpb3IuPC9saT4KPGxpPjxzdHJvbmc+Q29sb3F1ZSBvIGRldGVyZ2VudGU8L3N0cm9uZz4gKHBhc3RpbGhhIG91IHAmb2FjdXRlOykgbm8gY29tcGFydGltZW50byBuYSBwYXJ0ZSBpbnRlcm5hIGRhIHBvcnRhIGUgZmVjaGUuPC9saT4KPGxpPjxzdHJvbmc+QWJyYSBvIHJlZ2lzdHJvIGRlICZhYWN1dGU7Z3VhPC9zdHJvbmc+IGUgcHJlc3Npb25lIG8gYm90JmF0aWxkZTtvIGxpZ2EvZGVzbGlnYS48L2xpPgo8bGk+PHN0cm9uZz5Fc2NvbGhhIG8gcHJvZ3JhbWE8L3N0cm9uZz4gbm8gYm90JmF0aWxkZTtvIGRlIHByb2dyYW1hcy48L2xpPgo8bGk+PHN0cm9uZz5GZWNoZSBhIHBvcnRhLjwvc3Ryb25nPiBBIG0mYWFjdXRlO3F1aW5hIGNvbWUmY2NlZGlsO2Egc296aW5oYSB1bnMgMTAgc2VndW5kb3MgZGVwb2lzLjwvbGk+Cjwvb2w+CjxwPjxzdHJvbmc+UXVhbCBwcm9ncmFtYSBlc2NvbGhlcj88L3N0cm9uZz48L3A+Cjx1bD4KPGxpPjxzdHJvbmc+RUNPPC9zdHJvbmc+ICZtZGFzaDsgbyBkbyBkaWEgYSBkaWE6IGdhc3RhIG1lbm9zICZhYWN1dGU7Z3VhIGUgZW5lcmdpYS48L2xpPgo8bGk+PHN0cm9uZz5SJmFhY3V0ZTtwaWRvPC9zdHJvbmc+ICg0MCBtaW4pICZtZGFzaDsgcG91Y2EgbG91JmNjZWRpbDthIGUgcG91Y28gc3VqYSwgcXVhbmRvIHRlbSBwcmVzc2EuPC9saT4KPGxpPjxzdHJvbmc+SW50ZW5zaXZvPC9zdHJvbmc+ICZtZGFzaDsgcGFuZWxhcyBlIHRyYXZlc3NhcyBjb20gY29taWRhIHJlc3NlY2FkYS48L2xpPgo8bGk+PHN0cm9uZz4xIEhvcmE8L3N0cm9uZz4gb3UgPHN0cm9uZz5DcmlzdGFsPC9zdHJvbmc+ICZtZGFzaDsgY2FyZ2EgbGV2ZTsgQ3Jpc3RhbCAmZWFjdXRlOyBtYWlzIHN1YXZlIHBhcmEgdGEmY2NlZGlsO2FzLjwvbGk+CjwvdWw+CjxwPkFvIHRlcm1pbmFyLCBhcGl0YS4gRGVzbGlndWUgbyBhcGFyZWxobyBlIDxzdHJvbmc+ZXNwZXJlIHVucyAxNSBtaW51dG9zIGFudGVzIGRlIHJldGlyYXIgYSBsb3UmY2NlZGlsO2E8L3N0cm9uZz46IGVsYSBzYWkgYmVtIHF1ZW50ZSBlIHRlcm1pbmEgZGUgc2VjYXIgbmVzc2UgdGVtcG8uIEVzdmF6aWUgcHJpbWVpcm8gbyBjZXN0byBkZSBiYWl4byBwYXJhIG8gZGUgY2ltYSBuJmF0aWxkZTtvIHBpbmdhciBuZWxlLjwvcD4KPHA+JkVhY3V0ZTsgbm9ybWFsIGZpY2FyIHVtIHBvdWNvIGRlIHVtaWRhZGUgcG9yIGRlbnRyby4gU2UgdXNhciBwYXN0aWxoYXMsIHByZWZpcmEgcHJvZ3JhbWFzIGxvbmdvczogbm8gUiZhYWN1dGU7cGlkbyBlbGFzIG4mYXRpbGRlO28gc2UgZGlzc29sdmVtIHBvciBjb21wbGV0by48L3A+CjxwIHN0eWxlPSJiYWNrZ3JvdW5kOiNmNmY3ZmI7Ym9yZGVyOjFweCBzb2xpZCAjZTZlOGVmO2JvcmRlci1sZWZ0OjRweCBzb2xpZCAjZTg0MzkzO2JvcmRlci1yYWRpdXM6MTJweDtwYWRkaW5nOjE0cHggMTZweDttYXJnaW46MTJweCAwIj48c3Ryb25nPiYjMTI4MTk2OyBNYW51YWwgY29tcGxldG8gZG8gbGF2YS1sb3UmY2NlZGlsO2FzPC9zdHJvbmc+PGJyPjxhIGhyZWY9Il9fUERGX1VSTF9fIiB0YXJnZXQ9Il9ibGFuayIgcmVsPSJub29wZW5lciI+QmFpeGFyIG8gbWFudWFsIFRla2EgTFA4IDg1MCAoUERGKTwvYT48YnI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTouODVyZW07Y29sb3I6IzZhNzE4NiI+SW5jbHVpIGEgdGFiZWxhIGNvbXBsZXRhIGRlIHByb2dyYW1hcywgbGltcGV6YSBkZSBmaWx0cm9zIGUgYyZvYWN1dGU7ZGlnb3MgZGUgZXJyby48L3NwYW4+PC9wPgo=' );
$HVKE_TIT_SEC = array( 'es' => 'Secadora de Ropa', 'en' => 'Clothes Dryer', 'ptbr' => 'Secadora de Roupas' );
$HVKE_PDF_URL = 'https://raw.githubusercontent.com/jpchs1/trabajos-varios/main/homvuk-deploy/manual-lavavajillas-teka.pdf';
$HVKE_PDF_MD5 = 'f33023bd3df9fd26c978613a5c37c936';
$HVKE_PDF_NOM = 'manual-lavavajillas-teka.pdf';
$HVKE_MARCA   = 'Teka LP8 850';

$HVKE_T_ELE = '/electrodom|appliances|eletrodom/i';
$HVKE_T_SEC = '/secadora|dryer/i';
$HVKE_A_ELE = '/cocina de inducci|induction|cozinha|indu&ccedil;/i';

/** Divide el contenido en bloques <h3>: array( titulo, html ). */
function hvke_bloques( $html ) {
    $out = array();
    foreach ( preg_split( '/(?=<h3>)/i', (string) $html ) as $parte ) {
        if ( '' === trim( $parte ) ) {
            continue;
        }
        $titulo = '';
        if ( 0 === stripos( ltrim( $parte ), '<h3>' ) && preg_match( '#<h3>(.*?)</h3>#is', $parte, $m ) ) {
            $titulo = $m[1];
        }
        $out[] = array( $titulo, $parte );
    }
    return $out;
}

/** Devuelve el cuerpo (sin el <h3>) del primer bloque cuyo titulo coincida. */
function hvke_cuerpo( $html, $titulos ) {
    foreach ( hvke_bloques( $html ) as $b ) {
        if ( $b[0] && preg_match( $titulos, $b[0] ) ) {
            return trim( preg_replace( '#<h3>.*?</h3>#is', '', $b[1], 1 ) );
        }
    }
    return '';
}

/**
 * Reemplaza el bloque indicado; si no existe, lo inserta tras el bloque ancla
 * (o al final). Opcionalmente elimina otros bloques por titulo.
 */
function hvke_aplicar( $html, $nuevo, $titulos, $ancla = '', $eliminar = '' ) {
    if ( ! is_string( $html ) || '' === trim( $html ) ) {
        return array( null, 'contenido-vacio' );
    }
    $salida         = '';
    $reemplazado    = false;
    $posicion_ancla = null;
    foreach ( hvke_bloques( $html ) as $b ) {
        list( $titulo, $parte ) = $b;
        if ( $titulo && $eliminar && preg_match( $eliminar, $titulo ) ) {
            continue;
        }
        if ( $titulo && preg_match( $titulos, $titulo ) ) {
            if ( ! $reemplazado ) {
                $salida     .= $nuevo;
                $reemplazado = true;
            }
            continue;
        }
        $salida .= $parte;
        if ( ! $reemplazado && null === $posicion_ancla && $ancla && $titulo && preg_match( $ancla, $titulo ) ) {
            $posicion_ancla = strlen( $salida );
        }
    }
    if ( $reemplazado ) {
        return array( $salida, 'reemplazado' );
    }
    if ( null !== $posicion_ancla ) {
        return array( substr( $salida, 0, $posicion_ancla ) . $nuevo . substr( $salida, $posicion_ancla ), 'insertado' );
    }
    return array( $salida . $nuevo, 'insertado-al-final' );
}

// ---- Pruebas locales ----
if ( getenv( 'HVKE_TEST' ) ) {
    $fail = 0;
    $chk  = function ( $l, $c ) use ( &$fail ) { echo ( $c ? "[OK]    " : "[ERROR] " ) . $l . "\n"; if ( ! $c ) { $fail++; } };

    $base = str_replace( '__PDF_URL__', 'https://homvuk.com/wp-content/uploads/portal/manual.pdf', base64_decode( $GLOBALS['HVKE_BLOQUES']['es'] ) );
    $chk( 'bloque: titulo, pasos y enlace al manual', strpos( $base, '<h3>Electrodom' ) !== false && strpos( $base, '<ol>' ) !== false && strpos( $base, 'manual.pdf' ) !== false );
    $chk( 'bloque: sin marcadores pendientes', strpos( $base, '__PDF_URL__' ) === false );
    $chk( 'bloque: nombra los programas reales del equipo', strpos( $base, 'ECO' ) !== false && strpos( $base, 'Intensivo' ) !== false && strpos( $base, 'Cristal' ) !== false );

    // Unidad CON secadora (1506): su bloque se mueve dentro
    $u1506 = '<h3>Bienvenido</h3><p>x</p>'
        . '<h3>Secadora de Ropa</h3><p>Fensa o Bosch, vaciar estanque</p><p><a href="/v.mp4">Ver video</a></p>'
        . '<h3>Calefacci&oacute;n</h3><p>losa radiante</p>'
        . '<h3>Cocina de Inducci&oacute;n</h3><p>induccion</p>'
        . '<h3>Supermercados y Servicios Cercanos</h3><p>Jumbo</p>';
    $cuerpo = hvke_cuerpo( $u1506, $GLOBALS['HVKE_T_SEC'] );
    $chk( 'extrae el cuerpo de la secadora con su video', strpos( $cuerpo, 'Fensa o Bosch' ) !== false && strpos( $cuerpo, 'v.mp4' ) !== false && strpos( $cuerpo, '<h3>' ) === false );

    $bloque = $base . '<h4>' . $GLOBALS['HVKE_TIT_SEC']['es'] . '</h4>' . $cuerpo;
    list( $r, $m ) = hvke_aplicar( $u1506, $bloque, $GLOBALS['HVKE_T_ELE'], $GLOBALS['HVKE_A_ELE'], $GLOBALS['HVKE_T_SEC'] );
    $chk( '1506: bloque insertado tras Cocina de Induccion', 'insertado' === $m && strpos( $r, 'induccion' ) < strpos( $r, 'Electrodom' ) );
    $chk( '1506: queda antes de Supermercados', strpos( $r, 'Electrodom' ) < strpos( $r, 'Jumbo' ) );
    $chk( '1506: la secadora ya no es un bloque suelto', 0 === preg_match( '#<h3>\s*Secadora de Ropa\s*</h3>#i', $r ) );
    $chk( '1506: la secadora quedo dentro, con su video', strpos( $r, '<h4>Secadora de Ropa</h4>' ) !== false && strpos( $r, 'v.mp4' ) !== false );
    $chk( '1506: conserva calefaccion y bienvenida', strpos( $r, 'losa radiante' ) !== false && strpos( $r, '<h3>Bienvenido</h3>' ) === 0 );
    $chk( '1506: 5 bloques (entra electro, sale secadora)', 5 === substr_count( strtolower( $r ), '<h3>' ) );

    // Segunda pasada: no duplica
    $cuerpo2 = hvke_cuerpo( $r, $GLOBALS['HVKE_T_SEC'] );
    $chk( 'segunda pasada: ya no hay bloque suelto que mover', '' === $cuerpo2 );
    list( $r2, $m2 ) = hvke_aplicar( $r, $bloque, $GLOBALS['HVKE_T_ELE'], $GLOBALS['HVKE_A_ELE'], $GLOBALS['HVKE_T_SEC'] );
    $chk( 'idempotente: reemplaza, no agrega', 'reemplazado' === $m2 && 5 === substr_count( strtolower( $r2 ), '<h3>' ) );
    $chk( 'idempotente: un solo bloque de electrodomesticos', 1 === preg_match_all( '#<h3>\s*Electrodom#i', $r2 ) );
    $chk( 'idempotente: contenido identico al de la primera pasada', $r2 === $r );

    // Unidad SIN secadora (306)
    $u306 = '<h3>Bienvenido</h3><p>x</p><h3>Cocina de Inducci&oacute;n</h3><p>induccion</p><h3>Basura y Reciclaje</h3><p>shaft</p>';
    $chk( '306: no hay secadora que mover', '' === hvke_cuerpo( $u306, $GLOBALS['HVKE_T_SEC'] ) );
    list( $r3, $m3 ) = hvke_aplicar( $u306, $base, $GLOBALS['HVKE_T_ELE'], $GLOBALS['HVKE_A_ELE'], $GLOBALS['HVKE_T_SEC'] );
    $chk( '306: insertado tras Cocina de Induccion', 'insertado' === $m3 && strpos( $r3, 'induccion' ) < strpos( $r3, 'Electrodom' ) && strpos( $r3, 'Electrodom' ) < strpos( $r3, 'shaft' ) );
    $chk( '306: sin seccion de secadora', stripos( $r3, 'Secadora' ) === false );

    // Sin bloque ancla: va al final pero no se pierde nada
    list( $r4, $m4 ) = hvke_aplicar( '<h3>Solo esto</h3><p>x</p>', $base, $GLOBALS['HVKE_T_ELE'], $GLOBALS['HVKE_A_ELE'], $GLOBALS['HVKE_T_SEC'] );
    $chk( 'sin ancla: al final, conservando lo previo', 'insertado-al-final' === $m4 && strpos( $r4, 'Solo esto' ) !== false && strpos( $r4, 'Electrodom' ) !== false );

    foreach ( array( 'en' => 'Appliances', 'ptbr' => 'Eletrodom' ) as $l => $titulo ) {
        $b = str_replace( '__PDF_URL__', 'x.pdf', base64_decode( $GLOBALS['HVKE_BLOQUES'][ $l ] ) );
        $chk( "$l: titulo y enlace correctos", strpos( $b, $titulo ) !== false && strpos( $b, 'x.pdf' ) !== false && strpos( $b, '__PDF_URL__' ) === false );
    }

    echo $fail ? "FALLARON $fail pruebas\n" : "TODAS LAS PRUEBAS PASARON\n";
    exit( $fail ? 1 : 0 );
}

// ---- Ejecucion real ----
require __DIR__ . '/wp-load.php';
global $wpdb;

$mode  = isset( $argv[1] ) ? $argv[1] : 'install';
$tabla = $wpdb->prefix . 'portal_units';
$ok    = array();
$warn  = array();

if ( 'restore' === $mode ) {
    $b = get_option( 'hvkp_electro_backup' );
    if ( ! is_array( $b ) || empty( $b ) ) {
        echo "[AVISO] No hay respaldo; nada que restaurar.\n";
        exit( 0 );
    }
    foreach ( $b as $fila ) {
        $wpdb->update( $tabla, array( $fila['campo'] => $fila['valor'] ), array( 'id' => (int) $fila['id'] ) );
    }
    echo "[OK]    Contenido anterior restaurado (" . count( $b ) . " campos)\n";
    exit( 0 );
}

// 1) Manual del lavavajillas
$updir   = wp_upload_dir();
$destino = trailingslashit( $updir['basedir'] ) . 'portal/' . $HVKE_PDF_NOM;
$pdf_url = trailingslashit( $updir['baseurl'] ) . 'portal/' . $HVKE_PDF_NOM;
if ( ! is_dir( dirname( $destino ) ) ) {
    wp_mkdir_p( dirname( $destino ) );
}
if ( file_exists( $destino ) && md5_file( $destino ) === $HVKE_PDF_MD5 ) {
    $ok[] = 'El manual ya estaba publicado (' . $pdf_url . ')';
} else {
    $res = wp_remote_get( $HVKE_PDF_URL, array( 'timeout' => 120, 'sslverify' => false ) );
    if ( is_wp_error( $res ) ) {
        echo "[ERROR] No se pudo descargar el manual: " . $res->get_error_message() . "\n";
        exit( 1 );
    }
    $cuerpo_pdf = wp_remote_retrieve_body( $res );
    if ( md5( $cuerpo_pdf ) !== $HVKE_PDF_MD5 ) {
        echo "[ERROR] El manual descargado no coincide con el esperado (repo privado o descarga incompleta). Nada fue modificado.\n";
        exit( 1 );
    }
    file_put_contents( $destino, $cuerpo_pdf );
    $ok[] = 'Manual publicado: ' . $pdf_url . ' (' . round( strlen( $cuerpo_pdf ) / 1048576, 1 ) . ' MB)';
}

// 2) Bloque de electrodomesticos por unidad e idioma
$unidades = $wpdb->get_results( "SELECT id, name, slug FROM $tabla WHERE active = 1" );
if ( ! $unidades ) {
    echo "[ERROR] No se encontraron unidades activas.\n";
    exit( 1 );
}
$backup = array();
foreach ( $unidades as $u ) {
    foreach ( array( 'es', 'en', 'ptbr' ) as $lang ) {
        $campo = 'general_' . $lang;
        $antes = (string) $wpdb->get_var( $wpdb->prepare( "SELECT `$campo` FROM $tabla WHERE id = %d", $u->id ) );
        if ( '' === trim( $antes ) ) {
            $warn[] = "$u->name [$lang]: contenido vacio, se omite";
            continue;
        }
        $bloque = str_replace( '__PDF_URL__', esc_url( $pdf_url ), base64_decode( $HVKE_BLOQUES[ $lang ] ) );
        $detalle = 'lavavajillas Teka';

        // Si hay bloque suelto de secadora, se mueve dentro
        $cuerpo_sec = hvke_cuerpo( $antes, $HVKE_T_SEC );
        if ( $cuerpo_sec ) {
            $bloque .= '<h4>' . $HVKE_TIT_SEC[ $lang ] . '</h4>' . $cuerpo_sec;
            $detalle .= ' + secadora movida dentro';
        }

        list( $nuevo, $motivo ) = hvke_aplicar( $antes, $bloque, $HVKE_T_ELE, $HVKE_A_ELE, $HVKE_T_SEC );
        if ( null === $nuevo || $nuevo === $antes ) {
            $ok[] = "$u->name [$lang]: ya estaba al dia";
            continue;
        }
        $backup[] = array( 'id' => (int) $u->id, 'campo' => $campo, 'valor' => $antes );
        $wpdb->update( $tabla, array( $campo => $nuevo ), array( 'id' => (int) $u->id ) );
        $ok[] = "$u->name [$lang]: Electrodomesticos ($detalle, $motivo)";
    }
}
if ( $backup ) {
    update_option( 'hvkp_electro_backup', $backup, false );
}

if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
do_action( 'litespeed_purge_all' );
if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); }
if ( function_exists( 'w3tc_flush_all' ) ) { w3tc_flush_all(); }
$ok[] = 'Caches purgadas';

$vres = wp_remote_head( $pdf_url, array( 'timeout' => 30, 'sslverify' => false ) );
if ( ! is_wp_error( $vres ) && 200 === (int) wp_remote_retrieve_response_code( $vres ) ) {
    $ok[] = 'Verificado: el manual se descarga correctamente desde el portal';
} else {
    $warn[] = 'No se pudo verificar la descarga del manual; revisar la URL';
}

echo "== HOMVUK Portal - Electrodomesticos ==\n";
foreach ( $ok as $m )   { echo "[OK]    $m\n"; }
foreach ( $warn as $m ) { echo "[AVISO] $m\n"; }
echo "Listo. Este instalador puede borrarse: rm -f hvkp-electro.php\n";
echo "Para revertir: php hvkp-electro.php restore\n";
