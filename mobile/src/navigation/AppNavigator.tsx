import { NavigationContainer } from "@react-navigation/native";
import { createNativeStackNavigator } from "@react-navigation/native-stack";

import { HomeScreen } from "../screens/HomeScreen";
import { LoginScreen } from "../screens/LoginScreen";
import { useAuthStore } from "../store/auth-store";

type RootStackParamList = {
  Login: undefined;
  Home: undefined;
};

const Stack = createNativeStackNavigator<RootStackParamList>();

export function AppNavigator() {
  const token = useAuthStore((state) => state.token);

  return (
    <NavigationContainer>
      <Stack.Navigator>
        {token ? (
          <Stack.Screen
            name="Home"
            component={HomeScreen}
            options={{ title: "Portuguesando" }}
          />
        ) : (
          <Stack.Screen
            name="Login"
            component={LoginScreen}
            options={{ title: "Entrar" }}
          />
        )}
      </Stack.Navigator>
    </NavigationContainer>
  );
}
