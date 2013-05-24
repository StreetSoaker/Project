package com.md1a.android.streetsoaker;


import com.google.android.gms.common.ConnectionResult;
import com.google.android.gms.common.GooglePlayServicesClient;
import com.google.android.gms.common.GooglePlayServicesUtil;
import com.google.android.gms.location.LocationClient;
import com.google.android.gms.location.LocationListener;
import com.google.android.gms.location.LocationRequest;
import android.content.IntentSender;
import android.hardware.Sensor;
import android.hardware.SensorEvent;
import android.hardware.SensorEventListener;
import android.hardware.SensorManager;
import android.location.Location;
import android.net.Uri;
import android.os.Bundle;
import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.AlertDialog;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.res.Configuration;
import android.util.Log;
import android.view.KeyEvent;
import android.webkit.JsResult;
import android.webkit.WebChromeClient;
import android.webkit.WebView;
import android.webkit.WebViewClient;


public class MainActivity extends Activity implements
        GooglePlayServicesClient.ConnectionCallbacks,
        GooglePlayServicesClient.OnConnectionFailedListener, 
        LocationListener, 
        SensorEventListener {
	
    float[] mGravity;
    float[] mGeomagnetic;
	private double azimuth;
    private double roll;
    private double pitch;
	private WebView WebView;  
    private LocationRequest mLocationRequest;
    private LocationClient mLocationClient;
    private SensorManager mSensorManager;
    Sensor accelerometer;
    Sensor magnetometer;
   
    SharedPreferences mPrefs;
    SharedPreferences.Editor mEditor;

    boolean mUpdatesRequested = false;

    @SuppressLint("JavascriptInterface") @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        mSensorManager = (SensorManager)getSystemService(SENSOR_SERVICE);
	    accelerometer = mSensorManager.getDefaultSensor(Sensor.TYPE_ACCELEROMETER);
	    magnetometer = mSensorManager.getDefaultSensor(Sensor.TYPE_MAGNETIC_FIELD);
	    
        mLocationRequest = LocationRequest.create();
        mLocationRequest.setInterval(LocationUtils.UPDATE_INTERVAL_IN_MILLISECONDS);
        mLocationRequest.setPriority(LocationRequest.PRIORITY_HIGH_ACCURACY);
        mLocationRequest.setFastestInterval(LocationUtils.FAST_INTERVAL_CEILING_IN_MILLISECONDS);
        mUpdatesRequested = false;
        mPrefs = getSharedPreferences(LocationUtils.SHARED_PREFERENCES, Context.MODE_PRIVATE);
        mEditor = mPrefs.edit();
        mLocationClient = new LocationClient(this, this, this);
        
        WebView = (WebView) findViewById(R.id.webview);
		WebView.getSettings().setJavaScriptEnabled(true);
		WebView.getSettings().setLoadWithOverviewMode(true);
		WebView.getSettings().setUseWideViewPort(false);
		WebView.setWebViewClient(new MyWebViewClient());
		WebView.setWebChromeClient(new MyWebChromeClient());
		WebView.addJavascriptInterface(new JavaScriptInterface(this), "Android");
		WebView.setVerticalScrollBarEnabled(false);
		WebView.setHorizontalScrollBarEnabled(false);
 
		WebView.loadUrl("http://waxdt.com/locationnew.html");

	}
    
    @Override
    public void onConfigurationChanged(Configuration newConfig){        
        super.onConfigurationChanged(newConfig);
    }
    
    @Override
    public void onStop() {
        if (mLocationClient.isConnected()) {
        	
        }
     
        mLocationClient.disconnect();
        super.onStop();
    }
    
    @Override
    public void onPause() {

        mEditor.putBoolean(LocationUtils.KEY_UPDATES_REQUESTED, mUpdatesRequested);
        mEditor.commit();
        mSensorManager.unregisterListener(this);
        super.onPause();
    }

    @Override
    public void onStart() {

        super.onStart();
        mLocationClient.connect();

    }

    @Override
    public void onResume() {
        super.onResume();
        mSensorManager.registerListener(this, accelerometer, SensorManager.SENSOR_DELAY_UI);
        mSensorManager.registerListener(this, magnetometer, SensorManager.SENSOR_DELAY_UI);
       
        if (mPrefs.contains(LocationUtils.KEY_UPDATES_REQUESTED)) {
            mUpdatesRequested = mPrefs.getBoolean(LocationUtils.KEY_UPDATES_REQUESTED, false);   
        } else {
            mEditor.putBoolean(LocationUtils.KEY_UPDATES_REQUESTED, false);
            mEditor.commit();
        }
    }


    private boolean servicesConnected() {
	    int resultCode = GooglePlayServicesUtil.isGooglePlayServicesAvailable(this);
        if (ConnectionResult.SUCCESS == resultCode) {
            return true;
        } else {
            return false;
        }
    }

    @Override
    public void onConnected(Bundle bundle) {
        if (mUpdatesRequested) {
            
        }
    }

    @Override
    public void onDisconnected() {
        
    }

    @Override
    public void onConnectionFailed(ConnectionResult connectionResult) {
        if (connectionResult.hasResolution()) {
            try {
                connectionResult.startResolutionForResult(
                        this,
                        LocationUtils.CONNECTION_FAILURE_RESOLUTION_REQUEST);


            } catch (IntentSender.SendIntentException e) {
            	e.printStackTrace();
            }
        }
    }
    
    public void startUpdates() {
        mUpdatesRequested = true;

        if (servicesConnected()) {
            startPeriodicUpdates();
        }
    }

    public void stopUpdates() {
        mUpdatesRequested = false;

        if (servicesConnected()) {
            stopPeriodicUpdates();
        }
    }
    
    private void startPeriodicUpdates() {
        mLocationClient.requestLocationUpdates(mLocationRequest, this);
    }

    private void stopPeriodicUpdates() {
        mLocationClient.removeLocationUpdates(this);

    }
    
    public void onLocationChanged(Location location) {

    }
	
    public void onAccuracyChanged(Sensor sensor, int accuracy) {  
    	
    }
    
    public void onSensorChanged(SensorEvent event) {
    	if (event.sensor.getType() == Sensor.TYPE_ACCELEROMETER){
    		mGravity = event.values;
    	}
    	
    	if (event.sensor.getType() == Sensor.TYPE_MAGNETIC_FIELD) {
    		mGeomagnetic = event.values;
    	}
    	
    	if (mGravity != null && mGeomagnetic != null) {
    		float R[] = new float[9];
    		float I[] = new float[9];
    		boolean success = SensorManager.getRotationMatrix(R, I, mGravity, mGeomagnetic);
    		
    		if (success) {
    			float orientation[] = new float[3];
    			SensorManager.getOrientation(R, orientation);
    			float azimuthInRadians = orientation[0];
    			float azimuthInDegrees = (float)Math.toDegrees(azimuthInRadians);
    			
    			if (azimuthInDegrees < 0.0f) {
    				azimuthInDegrees += 360.0f;
    			}
    			
    			azimuth = azimuthInDegrees;
    			pitch = Math.toDegrees(orientation[1]);
    			roll = Math.toDegrees(orientation[2]);
    		}
    	}
	}
    
	private class MyWebViewClient extends WebViewClient {
		
    	@Override
		public boolean shouldOverrideUrlLoading(WebView view, String url) {
			if (Uri.parse(url).getHost().equals("http://waxdt.com")) {
				return false;
			}
			Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
			startActivity(intent);
			return true;
		}
	}
	  
	private class MyWebChromeClient extends WebChromeClient {
	      
		@Override
		public boolean onJsAlert(WebView view, String url, String message, JsResult result) {
			new AlertDialog.Builder(view.getContext())
		         .setMessage(message).setCancelable(true).show();
		         result.confirm();
		         return true;
			}
	 
	}
	 
	public class JavaScriptInterface {
		Context mContext;
	 
		JavaScriptInterface(Context c) {
	    	 mContext = c;
		}
		
		public Location getLocation() { 
			return mLocationClient.getLastLocation();
		}
		
		public double getLatitude() {
			return mLocationClient.getLastLocation().getLatitude();
		}
		
		public double getLongitude() {
			return mLocationClient.getLastLocation().getLongitude();
		}
		
		public float getAccuracy() {
			return mLocationClient.getLastLocation().getAccuracy();
		}
		
		public double getAzimuth() {
			return azimuth;
		}
		
		public double getPitch() {
			return pitch;
		}
		
		public double getRoll() {
			return roll;
		}
		
		public void startLocationUpdates() {
			startUpdates();
		}
		
		public void stopLocationUpdates() {
			stopUpdates();
		}
		
	}

	@Override
	public boolean onKeyDown(int keyCode, KeyEvent event) {
		if ((keyCode == KeyEvent.KEYCODE_BACK) && WebView.canGoBack()) {
			WebView.goBack();
			return true;
		}
		return super.onKeyDown(keyCode, event);
	}


    
}

