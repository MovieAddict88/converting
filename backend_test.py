import requests
import sys
import json
from datetime import datetime

class KaraokeAPITester:
    def __init__(self, base_url="https://vocalchamp.preview.emergentagent.com"):
        self.base_url = base_url
        self.api_url = f"{base_url}/api"
        self.token = None
        self.tests_run = 0
        self.tests_passed = 0
        self.test_results = []

    def log_test(self, name, success, details=""):
        """Log test result"""
        self.tests_run += 1
        if success:
            self.tests_passed += 1
            print(f"✅ {name} - PASSED")
        else:
            print(f"❌ {name} - FAILED: {details}")
        
        self.test_results.append({
            "test": name,
            "success": success,
            "details": details
        })

    def run_test(self, name, method, endpoint, expected_status, data=None, headers=None):
        """Run a single API test"""
        url = f"{self.api_url}/{endpoint}"
        test_headers = {'Content-Type': 'application/json'}
        
        if headers:
            test_headers.update(headers)
        
        if self.token and 'Authorization' not in test_headers:
            test_headers['Authorization'] = f'Bearer {self.token}'

        print(f"\n🔍 Testing {name}...")
        print(f"   URL: {url}")
        
        try:
            if method == 'GET':
                response = requests.get(url, headers=test_headers, timeout=10)
            elif method == 'POST':
                response = requests.post(url, json=data, headers=test_headers, timeout=10)
            elif method == 'DELETE':
                response = requests.delete(url, headers=test_headers, timeout=10)

            success = response.status_code == expected_status
            details = f"Status: {response.status_code}"
            
            if not success:
                try:
                    error_data = response.json()
                    details += f", Response: {error_data}"
                except:
                    details += f", Response: {response.text[:200]}"
            
            self.log_test(name, success, details)
            
            if success:
                try:
                    return response.json()
                except:
                    return {"status": "success"}
            return None

        except requests.exceptions.RequestException as e:
            self.log_test(name, False, f"Request error: {str(e)}")
            return None

    def test_admin_login(self):
        """Test admin authentication"""
        print("\n" + "="*50)
        print("TESTING ADMIN AUTHENTICATION")
        print("="*50)
        
        # Test valid login
        response = self.run_test(
            "Admin Login (Valid Credentials)",
            "POST",
            "auth/login",
            200,
            data={"username": "admin", "password": "admin123"}
        )
        
        if response and 'token' in response:
            self.token = response['token']
            print(f"   Token received: {self.token[:20]}...")
            return True
        
        # Test invalid login
        self.run_test(
            "Admin Login (Invalid Credentials)",
            "POST",
            "auth/login",
            401,
            data={"username": "admin", "password": "wrong"}
        )
        
        return False

    def test_songs_crud(self):
        """Test song CRUD operations"""
        print("\n" + "="*50)
        print("TESTING SONG CRUD OPERATIONS")
        print("="*50)
        
        # Test get songs (empty initially)
        songs = self.run_test(
            "Get Songs (Initial)",
            "GET",
            "songs",
            200
        )
        
        # Test create song (requires auth)
        song_data = {
            "title": "Test Karaoke Song",
            "artist": "Test Artist",
            "source_type": "youtube",
            "source_url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
            "thumbnail": "https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg"
        }
        
        created_song = self.run_test(
            "Create Song",
            "POST",
            "songs",
            200,
            data=song_data
        )
        
        song_id = None
        if created_song and 'id' in created_song:
            song_id = created_song['id']
            print(f"   Created song ID: {song_id}")
        
        # Test get specific song
        if song_id:
            self.run_test(
                "Get Specific Song",
                "GET",
                f"songs/{song_id}",
                200
            )
        
        # Test get all songs (should have 1 now)
        updated_songs = self.run_test(
            "Get Songs (After Creation)",
            "GET",
            "songs",
            200
        )
        
        # Test search songs
        self.run_test(
            "Search Songs",
            "GET",
            "songs?search=Test",
            200
        )
        
        # Test delete song
        if song_id:
            self.run_test(
                "Delete Song",
                "DELETE",
                f"songs/{song_id}",
                200
            )
        
        return song_id

    def test_youtube_search(self):
        """Test YouTube search functionality"""
        print("\n" + "="*50)
        print("TESTING YOUTUBE SEARCH")
        print("="*50)
        
        # Test without API key (should fail)
        self.run_test(
            "YouTube Search (No API Key)",
            "GET",
            "youtube/search?q=karaoke",
            400
        )
        
        # Test with fake API key (should fail)
        self.run_test(
            "YouTube Search (Invalid API Key)",
            "GET",
            "youtube/search?q=karaoke&api_key=fake_key",
            400
        )

    def test_scores(self):
        """Test scoring system"""
        print("\n" + "="*50)
        print("TESTING SCORING SYSTEM")
        print("="*50)
        
        # Create a test song first
        song_data = {
            "title": "Score Test Song",
            "artist": "Test Artist",
            "source_type": "youtube",
            "source_url": "https://www.youtube.com/watch?v=test"
        }
        
        created_song = self.run_test(
            "Create Song for Score Test",
            "POST",
            "songs",
            200,
            data=song_data
        )
        
        song_id = None
        if created_song and 'id' in created_song:
            song_id = created_song['id']
        
        if song_id:
            # Test create score
            score_data = {
                "song_id": song_id,
                "player_name": "Test Player",
                "score": 850,
                "accuracy": 92.5
            }
            
            self.run_test(
                "Create Score",
                "POST",
                "scores",
                200,
                data=score_data
            )
            
            # Test get song scores
            self.run_test(
                "Get Song Scores",
                "GET",
                f"scores/{song_id}",
                200
            )
            
            # Test leaderboard
            self.run_test(
                "Get Leaderboard",
                "GET",
                "leaderboard",
                200
            )
            
            # Clean up - delete test song
            self.run_test(
                "Delete Score Test Song",
                "DELETE",
                f"songs/{song_id}",
                200
            )

    def test_file_operations(self):
        """Test file-related operations"""
        print("\n" + "="*50)
        print("TESTING FILE OPERATIONS")
        print("="*50)
        
        # Test file serving with non-existent file
        self.run_test(
            "Serve Non-existent File",
            "GET",
            "files/nonexistent",
            404
        )

    def run_all_tests(self):
        """Run all API tests"""
        print("🎤 KARAOKE API TESTING STARTED")
        print(f"Testing against: {self.base_url}")
        print("="*60)
        
        # Test admin authentication first
        if not self.test_admin_login():
            print("\n❌ CRITICAL: Admin login failed. Cannot proceed with authenticated tests.")
            return False
        
        # Test all other endpoints
        self.test_songs_crud()
        self.test_youtube_search()
        self.test_scores()
        self.test_file_operations()
        
        # Print summary
        print("\n" + "="*60)
        print("🎤 KARAOKE API TESTING SUMMARY")
        print("="*60)
        print(f"Tests Run: {self.tests_run}")
        print(f"Tests Passed: {self.tests_passed}")
        print(f"Tests Failed: {self.tests_run - self.tests_passed}")
        print(f"Success Rate: {(self.tests_passed/self.tests_run)*100:.1f}%")
        
        if self.tests_passed == self.tests_run:
            print("🎉 ALL TESTS PASSED!")
            return True
        else:
            print("⚠️  SOME TESTS FAILED")
            return False

def main():
    tester = KaraokeAPITester()
    success = tester.run_all_tests()
    
    # Save detailed results
    results = {
        "timestamp": datetime.now().isoformat(),
        "total_tests": tester.tests_run,
        "passed_tests": tester.tests_passed,
        "failed_tests": tester.tests_run - tester.tests_passed,
        "success_rate": (tester.tests_passed/tester.tests_run)*100 if tester.tests_run > 0 else 0,
        "test_details": tester.test_results
    }
    
    with open('/app/backend_test_results.json', 'w') as f:
        json.dump(results, f, indent=2)
    
    return 0 if success else 1

if __name__ == "__main__":
    sys.exit(main())