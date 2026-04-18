import { AppRouter } from './routes/AppRouter.jsx';
import { Toaster } from 'react-hot-toast';
import { GlobalSpinner } from './components/common/GlobalSpinner';
import { useLoading } from './context/LoadingContext';

function App() {
  const { isLoading } = useLoading();

  return (
    <>
      <AppRouter />
      <Toaster position="top-right" />
      {isLoading ? <GlobalSpinner /> : null}
    </>
  );
}

export default App;
