#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>

const char* ssid[] = {"Raspberry", "WiFi2", "WiFi3"};        // List of SSIDs
const char* password[] = {"Halloween23", "Password2", "Password3"};  // Corresponding passwords
const int numNetworks = 3;  // Total number of Wi-Fi networks to try

const char* serverUrl = "http://aqi.trubros.co.in/aqi_data_insert.php"; // URL to PHP script

// Use const char* for string data types
const char* ssid_name;
const char* location;
int aqi_data;

WiFiClient client;  // Create a WiFiClient object for the HTTP request
const int ledPin = LED_BUILTIN;  // Onboard LED pin (GPIO2/D4)

void connectToWiFi() {
