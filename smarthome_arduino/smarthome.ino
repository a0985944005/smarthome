#include "DHT.h"
#define DHTPIN 2     // 接角PWM 2
#define DHTTYPE DHT22   // 溫溼度模組DHT 22  (AM2302), AM2321
#define MQ_PIN                      (0)
#define RL_VALUE                    (5)
#define RO_CLEAN_AIR_FACTOR         (9.83)
#define CALIBARAION_SAMPLE_TIMES    (50)
#define CALIBRATION_SAMPLE_INTERVAL (500)
#define READ_SAMPLE_INTERVAL        (50)  
#define READ_SAMPLE_TIMES           (5)
#define GAS_LPG                     (0)
#define GAS_CO                      (1)
#define GAS_SMOKE                   (2)
#include <Bridge.h>
#include <Console.h>
#include <FileIO.h>
#include <HttpClient.h>
#include <Mailbox.h>
#include <Process.h>
#include <YunClient.h>
#include <YunServer.h>
#include <SPI.h>

DHT dht(DHTPIN, DHTTYPE);
IPAddress server(192,168,0,133);
YunServer yunserver;
YunClient client;
String parametri ="";  //String of POST parameters
int buzzerPin=4;
float LPGCurve[3]={2.3,0.21,-0.47};
float COCurve[3]={2.3,0.72,-0.34};
float  SmokeCurve[3]={2.3,0.53,-0.44};
float  Ro=10;
void setup() {
    pinMode(13,OUTPUT);
    pinMode(12,OUTPUT);
    pinMode(11,OUTPUT);
    pinMode(10,OUTPUT);
    pinMode(9,OUTPUT);
    pinMode(8,OUTPUT);
    pinMode(buzzerPin, OUTPUT);
    Serial.begin(9600);
    dht.begin();
    Bridge.begin();
    yunserver.listenOnLocalhost();
    yunserver.begin();
    Ro = MQCalibration(MQ_PIN);
}

void loop() {
  
    /*--DHT start--*/
    float h = dht.readHumidity();
    float t = dht.readTemperature();
    float f = dht.readTemperature(true);
    float lpg = MQGetGasPercentage(MQRead(MQ_PIN)/Ro,GAS_LPG);
    float co = MQGetGasPercentage(MQRead(MQ_PIN)/Ro,GAS_CO);
    float smoke = MQGetGasPercentage(MQRead(MQ_PIN)/Ro,GAS_SMOKE);
///////server to arduino
    YunClient client = yunserver.accept();
    if (client) {
        process(client);
        client.stop();
    }
///////////
    // Check if any reads failed and exit early (to try again).
    if (isnan(h) || isnan(t) || isnan(f)) {
      Serial.println("Failed to read from DHT sensor!");
      return;
    }
  
    // Compute heat index in Fahrenheit (the default)
    float hif = dht.computeHeatIndex(f, h);
    // Compute heat index in Celsius (isFahreheit = false)
    float hic = dht.computeHeatIndex(t, h, false);
    /*--DHT end--*/

    /*R0是一個標準所以要取100次的平均,而真正的直式當下傳進來的不需要這樣平均*/

    Serial.print("humidity: ");
    Serial.print(h);
    Serial.print(" %\t");
    Serial.print("temperature: ");
    Serial.print(t);
    Serial.print(" *C ");
    Serial.print(f);
    Serial.print(" *F\t");
    Serial.println("\n");
    Serial.print("LPG:"); 
    Serial.print(lpg);
    Serial.print( "ppm" );
    Serial.print("\t");   
    Serial.print("CO:"); 
    Serial.print(co);
    Serial.print( "ppm" );
    Serial.print("\t ");   
    Serial.print("SMOKE:"); 
    Serial.print(smoke);
    Serial.print( "ppm" );
    Serial.print("\n");

    if(co>200){
     alarmClockBeep(buzzerPin);
    }
    ////////////////POST STRAT//////////////////
     if (client.connect(server, 80)) {
//          Serial.println("connected");
//          delay(500);
          parametri="temperature="+String(t)+"&humidity="+String(h)+"&co="+String(co)+"&lpg="+String(lpg)+"&smoke="+String(smoke);
          client.println("POST /smarthome/arduinoyun.php HTTP/1.1");
          client.println("Host: 192.168.0.133");
          client.print("Content-length:");
          client.println(parametri.length());
//          Serial.println(parametri);
          client.println("Connection: Close");
          client.println("Content-Type: application/x-www-form-urlencoded;");
          client.println();
          client.println(parametri); 
     }else{
//          Serial.println("connection failed");
//          delay(500);
     }
     if(client.connected()){
             client.stop();   //disconnect from server
     } 
    }
 ////////////////POST END////////////////////

void process(YunClient client) {
    String command = client.readStringUntil('\r');
    if (command == "131") {
        digitalWrite(13, HIGH);
    }
    else if (command == "130") {
        digitalWrite(13, LOW);
    }
    else if (command == "121") {
        digitalWrite(12, HIGH);   
    }
    else if (command == "120") {
        digitalWrite(12, LOW);
    }
     else if (command == "111") {
        digitalWrite(11, HIGH);   
    }
    else if (command == "110") {
        digitalWrite(11, LOW);
    }
     else if (command == "101") {
        digitalWrite(10, HIGH);   
    }
    else if (command == "100") {
        digitalWrite(10, LOW);
    }
     else if (command == "91") {
        digitalWrite(9, HIGH);   
    }
    else if (command == "90") {
        digitalWrite(9, LOW);
    }
     else if (command == "81") {
        digitalWrite(8, HIGH);   
    }
    else if (command == "80") {
        digitalWrite(8, LOW);
    }
    
}

void alarmClockBeep(int pin) {
  tone(pin, 1000, 100);
  delay(200);
  tone(pin, 1000, 100);
  delay(200); 
  tone(pin, 1000, 100);
  delay(200);
  tone(pin, 1000, 100); 
  }
  
float MQResistanceCalculation(int raw_adc)
{
  return ( ((float)RL_VALUE*(1023-raw_adc)/raw_adc));
}

float MQCalibration(int mq_pin)
{
  int i;
  float val=0;

  for (i=0;i<CALIBARAION_SAMPLE_TIMES;i++) {            //take multiple samples
    val += MQResistanceCalculation(analogRead(mq_pin));
    delay(CALIBRATION_SAMPLE_INTERVAL);
  }
  val = val/CALIBARAION_SAMPLE_TIMES;                   //calculate the average value

  val = val/RO_CLEAN_AIR_FACTOR;                        //divided by RO_CLEAN_AIR_FACTOR yields the Ro 
                                                        //according to the chart in the datasheet 

  return val; 
}

float MQRead(int mq_pin)
{
  int i;
  float rs=0;

  for (i=0;i<READ_SAMPLE_TIMES;i++) {
    rs += MQResistanceCalculation(analogRead(mq_pin));
    delay(READ_SAMPLE_INTERVAL);
  }

  rs = rs/READ_SAMPLE_TIMES;

  return rs;  
}

int MQGetGasPercentage(float rs_ro_ratio, int gas_id)
{
  if ( gas_id == GAS_LPG ) {
     return MQGetPercentage(rs_ro_ratio,LPGCurve);
  } else if ( gas_id == GAS_CO ) {
     return MQGetPercentage(rs_ro_ratio,COCurve);
  } else if ( gas_id == GAS_SMOKE ) {
     return MQGetPercentage(rs_ro_ratio,SmokeCurve);
  }    

  return 0;
}

int  MQGetPercentage(float rs_ro_ratio, float *pcurve)
{
  return (pow(10,( ((log(rs_ro_ratio)-pcurve[1])/pcurve[2]) + pcurve[0])));
}
