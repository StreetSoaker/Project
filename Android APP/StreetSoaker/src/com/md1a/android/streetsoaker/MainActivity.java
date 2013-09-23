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
import android.app.Activity;
import android.app.AlertDialog;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.res.Configuration;
import android.view.KeyEvent;
import android.view.ViewGroup;
import android.view.ViewGroup.LayoutParams;
import android.webkit.GeolocationPermissions;
import android.webkit.JavascriptInterface;
import android.webkit.JsResult;
import android.webkit.WebChromeClient;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.FrameLayout;


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
    protected WebView webView;
    protected FrameLayout webViewPlaceholder;
    private LocationRequest mLocationRequest;
    private LocationClient mLocationClient;
    private SensorManager mSensorManager;
    Sensor accelerometer;
    Sensor magnetometer;
   
    SharedPreferences mPrefs;
    SharedPreferences.Editor mEditor;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        initUI();
        mSensorManager = (SensorManager)getSystemService(SENSOR_SERVICE);
	    accelerometer = mSensorManager.getDefaultSensor(Sensor.TYPE_ACCELEROMETER);
	    magnetometer = mSensorManager.getDefaultSensor(Sensor.TYPE_MAGNETIC_FIELD);
        mLocationRequest = LocationRequest.create();
        mLocationRequest.setPriority(LocationRequest.PRIORITY_HIGH_ACCURACY); 
        mLocationRequest.setNumUpdates(1);
        mPrefs = getSharedPreferences(LocationUtils.SHARED_PREFERENCES, Context.MODE_PRIVATE);
        mEditor = mPrefs.edit();
        mLocationClient = new LocationClient(this, this, this);
        mSensorManager.registerListener(this, accelerometer, SensorManager.SENSOR_DELAY_UI);
        mSensorManager.registerListener(this, magnetometer, SensorManager.SENSOR_DELAY_UI);
	}
    

	protected void initUI() {
    	webViewPlaceholder = ((FrameLayout)findViewById(R.id.webViewPlaceholder));
    	if (webView == null) {
    		webView = new WebView(this);
    	    webView.setLayoutParams(new ViewGroup.LayoutParams(LayoutParams.FILL_PARENT, LayoutParams.FILL_PARENT));
    	    webView.getSettings().setLoadsImagesAutomatically(true);
    	    webView.getSettings().setJavaScriptEnabled(true);
    		webView.getSettings().setLoadWithOverviewMode(true);
    		webView.getSettings().setUseWideViewPort(false);
    		webView.getSettings().setGeolocationEnabled(true);
    		webView.getSettings().setAppCacheEnabled(true);
    	    webView.getSettings().setDatabaseEnabled(true);
    	    webView.getSettings().setDomStorageEnabled(true);
    		webView.setWebViewClient(new MyWebViewClient());
    		webView.setWebChromeClient(new MyWebChromeClient());
    		webView.addJavascriptInterface(new JavaScriptInterface(this), "Android");
    		webView.setVerticalScrollBarEnabled(false);
    		webView.setHorizontalScrollBarEnabled(false);
    		webView.getSettings().setCacheMode(WebSettings.LOAD_NO_CACHE);
    		webView.setWebViewClient(new WebViewClient());
    		webView.loadUrl("http://waxdt.com/test.php");
    		
    	}
    	
    	webViewPlaceholder.addView(webView);
    }
    
    public void onConfigurationChanged(Configuration newConfig) {
    	if (webView != null) {
          webViewPlaceholder.removeView(webView);
        }
    	super.onConfigurationChanged(newConfig);
    	setContentView(R.layout.activity_main);
    	initUI();
    }
    
    @Override
    protected void onSaveInstanceState(Bundle outState) {
    	webView.saveState(outState);
    	super.onSaveInstanceState(outState);
	}
     
    @Override
    protected void onRestoreInstanceState(Bundle savedInstanceState) {
    	webView.restoreState(savedInstanceState);
    	super.onRestoreInstanceState(savedInstanceState);
    }
    
    @Override
    public void onStop() {
        super.onStop();
        webView.loadUrl("javascript:console.log('onStop')");
    }
    
    @Override
    public void onPause() {
        super.onPause();
        webView.loadUrl("javascript:console.log('onPause')");
    }

    @Override
    public void onStart() { 
        super.onStart();
        mLocationClient.connect();
        webView.loadUrl("javascript:console.log('onStart')");
    }

    @Override
    public void onResume() {
    	super.onResume();
    	webView.loadUrl("javascript:console.log('onResume')");
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
    	mLocationClient.requestLocationUpdates(mLocationRequest, this);
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
    
    public void onLocationChanged(Location location) {
    	
    }
    
    public void requestLocationUpdate() {
    	mLocationClient.requestLocationUpdates(mLocationRequest, this);
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
    			float azimuthInDegrees = (float)Math.toDegrees(azimuthInRadians) + 90.0f;
    			
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
		
		public void onGeolocationPermissionsShowPrompt(String origin, GeolocationPermissions.Callback callback) {
		    callback.invoke(origin, true, false);
		}
	 
	}
	 
	public class JavaScriptInterface {
		Context mContext;
	 
		JavaScriptInterface(Context c) {
	    	 mContext = c;
		}
		
		@JavascriptInterface
		public Location getLocation() { 
			return mLocationClient.getLastLocation();
		}
		
		@JavascriptInterface
		public double getLatitude() {
			return mLocationClient.getLastLocation().getLatitude();
		}
		
		@JavascriptInterface
		public double getLongitude() {
			return mLocationClient.getLastLocation().getLongitude();
		}
		
		@JavascriptInterface
		public double getAccuracy() {
			return mLocationClient.getLastLocation().getAccuracy();
		}
		
		
		@JavascriptInterface
		public double getAzimuth() {
			return azimuth;
		}
		
		@JavascriptInterface
		public double getPitch() {
			return pitch;
		}
		
		@JavascriptInterface
		public double getRoll() {
			return roll;
		}
		
		@JavascriptInterface
		public void updateLocation() {
			requestLocationUpdate();
		}
		
	}
	
	@Override
	public boolean onKeyDown(int keyCode, KeyEvent event) {
		if ((keyCode == KeyEvent.KEYCODE_BACK) && webView.canGoBack()) {
			webView.goBack();
			return true;
		}
		return super.onKeyDown(keyCode, event);
	}


    
}

