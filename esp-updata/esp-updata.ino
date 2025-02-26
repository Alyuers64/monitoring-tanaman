#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
#include <DHT.h>

// WiFi Credentials
const char* ssid = "Lia 110794";
const char* password = "MR28022024";
WiFiServer server(80);

// Laravel API URL
const char* sensorAPI = "http://192.168.1.2:8000/api/sensor-data";
const char* relayAPI = "http://192.168.1.2:8000/api/relay-status";

// Pin setup
const int relayPin = D4;
const int dhtPin = D2;
#define DHTTYPE DHT11
DHT dht(dhtPin, DHTTYPE);

void setup() {
    Serial.begin(115200);
    WiFi.begin(ssid, password);
    Serial.println("Menghubungkan ke WiFi...");
    while (WiFi.status() != WL_CONNECTED) {
        delay(1000);
        Serial.print(".");
    }
    Serial.println("\nWiFi Terhubung!");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
    
    server.begin();
    pinMode(relayPin, OUTPUT);
    digitalWrite(relayPin, LOW); 
    dht.begin();
}

void loop() {
    static unsigned long lastRead = 0;
    static unsigned long lastRelayCheck = 0;
    
    if (millis() - lastRead >= 5000) {
        lastRead = millis();
        sendSensorData();
    }
    
    if (millis() - lastRelayCheck >= 5000) {
        lastRelayCheck = millis();
        checkRelayStatus();
    }
}

void sendSensorData() {
    float suhu = dht.readTemperature();
    float kelembapan = dht.readHumidity();
    
    if (!isnan(suhu) && !isnan(kelembapan)) {
        Serial.print("Mengirim data ke Laravel: ");
        Serial.print(suhu);
        Serial.print(" °C, ");
        Serial.print(kelembapan);
        Serial.println(" %");

        if (WiFi.status() == WL_CONNECTED) {
            HTTPClient http;
            WiFiClient client;
            
            http.begin(client, sensorAPI);
            http.addHeader("Content-Type", "application/json");
            
            String jsonData = "{\"suhu\":" + String(suhu) + ",\"kelembapan\":" + String(kelembapan) + "}";
            int httpResponseCode = http.POST(jsonData);
            
            Serial.print("HTTP Response Code: ");
            Serial.println(httpResponseCode);
            http.end();
        }
    }
}

void checkRelayStatus() {
    if (WiFi.status() == WL_CONNECTED) {
        HTTPClient http;
        WiFiClient client;
        http.begin(client, relayAPI);
        
        int httpCode = http.GET();
        if (httpCode == HTTP_CODE_OK) {
            String payload = http.getString();
            Serial.print("Response Relay: ");
            Serial.println(payload);

            StaticJsonDocument<200> doc;
            deserializeJson(doc, payload);
            int relayStatus = doc["status"];

            Serial.print("Status Relay dari API: ");
            Serial.println(relayStatus);
            
            digitalWrite(relayPin, relayStatus ? HIGH : LOW);
        } else {
            Serial.print("Gagal mengambil status relay, kode: ");
            Serial.println(httpCode);
        }
        http.end();
    }
}
